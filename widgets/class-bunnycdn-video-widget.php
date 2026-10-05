<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (! defined('ABSPATH')) exit;

class BunnyCDN_Video_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'bunnycdn_video';
    }

    public function get_title()
    {
        return 'BunnyCDN Video';
    }

    public function get_icon()
    {
        return 'eicon-play';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['bunny', 'bunnycdn', 'video', 'stream', 'secure'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Video Settings', 'bunnycdn-video-widget'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'video_id',
            [
                'label' => __('Video ID', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => __('Enter BunnyCDN Video ID', 'bunnycdn-video-widget'),
            ]
        );

        $this->add_control(
            'library_id',
            [
                'label' => __('Library ID (optional)', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => get_option('bunnycdn_library_id') ?: '123456',
                'description' => __('Leave empty to use the Library ID from Settings > BunnyCDN.', 'bunnycdn-video-widget'),
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'muted',
            [
                'label' => __('Muted', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'loop',
            [
                'label' => __('Loop', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'token_expiration',
            [
                'label' => __('Token Expiration (minutes)', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::NUMBER,
                'default' => 180,
                'min' => 1,
                'max' => 1440,
                'description' => __('Default is 180 minutes (3 hours). Adjust if you need shorter or longer token lifetime.', 'bunnycdn-video-widget'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'bunnycdn-video-widget'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => __('Background', 'bunnycdn-video-widget'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .bunny-video-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'label' => __('Border', 'bunnycdn-video-widget'),
                'selector' => '{{WRAPPER}} .bunny-video-wrapper',
            ]
        );

        $this->add_control(
            'style_border_radius',
            [
                'label' => __('Border Radius', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .bunny-video-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'aspect_ratio',
            [
                'label' => __('Aspect Ratio', 'bunnycdn-video-widget'),
                'type' => Controls_Manager::SELECT,
                'default' => '56.25%', // 16:9
                'options' => [
                    '56.25%' => '16:9',
                    '75%'    => '4:3',
                    '100%'   => '1:1 (Square)',
                ],
                'selectors' => [
                    '{{WRAPPER}} .bunny-video-inner' => 'padding-top: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    // Setup problems are only explained to people who can edit the page; visitors see nothing.
    private function notice($message)
    {
        if (current_user_can('edit_posts')) {
            echo '<p class="bunny-video-notice">' . esc_html($message) . '</p>';
        }
    }

    protected function render()
    {
        $video_id = trim((string) $this->get_settings_for_display('video_id'));
        if (! $video_id) {
            $this->notice(__('Please enter a Video ID.', 'bunnycdn-video-widget'));
            return;
        }

        $library_id = trim((string) $this->get_settings_for_display('library_id')) ?: get_option('bunnycdn_library_id');
        if (! $library_id) {
            $this->notice(__('BunnyCDN Library ID not configured. Go to Settings > BunnyCDN.', 'bunnycdn-video-widget'));
            return;
        }

        $token_key = get_option('bunnycdn_token_key');
        if (! $token_key) {
            $this->notice(__('BunnyCDN Token Key not configured. Go to Settings > BunnyCDN.', 'bunnycdn-video-widget'));
            return;
        }

        // Bunny Stream embed token authentication: sha256(token key + video ID + expiration timestamp).
        $expiration_minutes = absint($this->get_settings_for_display('token_expiration')) ?: 180;
        $expires = time() + ($expiration_minutes * 60);
        $token = hash('sha256', $token_key . $video_id . $expires);

        $params = [
            'token'      => $token,
            'expires'    => $expires,
            'responsive' => 'true',
            'autoplay'   => $this->get_settings_for_display('autoplay') ? 'true' : 'false',
            'muted'      => $this->get_settings_for_display('muted') ? 'true' : 'false',
            'loop'       => $this->get_settings_for_display('loop') ? 'true' : 'false',
            'preload'    => 'true',
        ];

        $secure_url = 'https://iframe.mediadelivery.net/embed/' . rawurlencode($library_id) . '/' . rawurlencode($video_id) . '?' . http_build_query($params);

        // Base layout, printed once per page however many videos it has.
        static $styles_printed = false;
        if (! $styles_printed) {
            $styles_printed = true;
            ?>
            <style>
                .bunny-video-wrapper {
                    position: relative;
                    width: 100%;
                    overflow: hidden;
                }

                .bunny-video-inner {
                    position: relative;
                    width: 100%;
                    padding-top: 56.25%; /* overridden by the Aspect Ratio control */
                }

                .bunny-video-iframe {
                    border: 0;
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                }
            </style>
            <?php
        }
?>

        <div class="bunny-video-wrapper">
            <div class="bunny-video-inner">
                <iframe
                    id="bunnycdn-video-<?php echo esc_attr($video_id); ?>"
                    src="<?php echo esc_url($secure_url); ?>"
                    loading="lazy"
                    class="bunny-video-iframe"
                    allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;"
                    allowfullscreen="true">
                </iframe>
            </div>
        </div>

<?php
    }
}
