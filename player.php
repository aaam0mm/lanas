<?php
include 'init.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'default';

    if ($action === 'open') {
        $post_id = $_POST['post_id'] ?? 0;
        if ($post_id == 0) {
            die('no post id');
        }
        if(!isset($post_title)) {
            $post_title = get_post_field($post_id, "post_title");
        }
        $audios_ids = @unserialize(get_post_meta($post_id, "audios_ids"));
        $audio_files = [];
        if (is_array($audios_ids) && count($audios_ids) > 0) {
            foreach ($audios_ids as $audio_data) {
                // Decode the JSON string
                $audio_info = json_decode($audio_data, true);

                if ($audio_info && isset($audio_info['file_id'])) {
                    // Get the audio file URL using the get_file() function
                    $file_url = get_file($audio_info['file_id']);

                    // If the file exists, add it to the array
                    if ($file_url) {
                        $audio_files[] = [
                            'track_name' => $audio_info['track_name'],
                            'file_url' => $file_url
                        ];
                    }
                }
            }
            $_SESSION['audio_files'] = $audio_files;
            $_SESSION['player_book_title'] = [
                'book_title' => $post_title,
                'link' => get_post_link($post_id)
            ];
        }
    } elseif ($action === 'close') {
        // Clear session data to close the player
        unset($_SESSION['audio_files']);
        unset($_SESSION['player_book_title']);
        echo 'success'; // Return a success response
        exit;
    }
}

// Function to render the player
function render_player($audio_files) {
    if (!is_array($audio_files)) {
        return '';
    }

    $tracks_json = json_encode(array_map(function ($audio) {
        return [
            'title' => $audio['track_name'],
            'url' => $audio['file_url']
        ];
    }, $audio_files));

    ob_start();
    ?>
    <link rel="stylesheet" href="<?php echo siteurl() ?>/assets/css/audio/style.css">
    <link rel="stylesheet" href="<?php echo siteurl() ?>/assets/css/audio/player.css">

    <div class="audio-player shadow border">
        <div id="player-arg-control" class="btn-group">
            <button id="close-player" class="btn btn-default bg-white rounded-0 border">
                <i class="fas fa-times"></i>
            </button>
            <button id="mini-maxi" class="btn btn-default bg-white rounded-0 border">
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
        <div class="track-info row align-items-center maximized">
            <?php
            if(isset($_SESSION['player_book_title'])) {
                echo '<a class="mr-3" target="_blanc" href="'. $_SESSION['player_book_title']['link'] .'">'. $_SESSION['player_book_title']['book_title'] .'</a>';
            }
            ?>
            <span></span>
            <p class="track-title">انتظر...</p>
        </div>

        <div class="progress-container maximized">
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
            <div class="time-display">
                <span class="current-time">0:00</span>
                <span class="duration">0:00</span>
            </div>
        </div>

        <div class="controls flex-wrap">
            <div class="secondary-controls col-lg-7 maximized">
                <div class="speed-control mini-none">
                    <select class="speed-selector custom-select">
                        <option value="0.5">0.5x</option>
                        <option value="1" selected>1x</option>
                        <option value="1.5">1.5x</option>
                        <option value="2">2x</option>
                    </select>
                </div>

                <div class="volume-control">
                    <button class="mute-button">
                        <svg class="volume-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                        <svg class="mute-icon hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <line x1="23" y1="9" x2="17" y2="15"></line>
                            <line x1="17" y1="9" x2="23" y2="15"></line>
                        </svg>
                    </button>
                    <input type="range" class="volume-slider" min="0" max="1" step="0.1" value="1">
                </div>
            </div>

            <div class="main-controls col">
                <button class="prev-button maximized">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="19 20 9 12 19 4 19 20"></polygon>
                        <line x1="5" y1="19" x2="5" y2="5"></line>
                    </svg>
                </button>
                <button class="play-button">
                    <svg class="play-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                    <svg class="pause-icon hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="4" width="4" height="16"></rect>
                        <rect x="14" y="4" width="4" height="16"></rect>
                    </svg>
                </button>
                <button class="next-button maximized">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 4 15 12 5 20 5 4"></polygon>
                        <line x1="19" y1="5" x2="19" y2="19"></line>
                    </svg>
                </button>
            </div>

        </div>

        <div class="playlist maximized">
            <ul class="track-list"></ul>
        </div>
    </div>

    <script>
        // Ensure CONFIG is defined
        if (typeof CONFIG === 'undefined') {
            CONFIG = {};
        }
        CONFIG.tracks = <?php echo $tracks_json; ?>;

        // Ensure AudioController is defined
        if (typeof AudioController === 'undefined') {
            var audioController = new AudioController();
        } else {
            audioController = new AudioController();
        }

        // Ensure UIController is defined
        if (typeof UIController === 'undefined') {
            var uiController = new UIController(audioController);
        } else {
            uiController = new UIController(audioController);
        }

        // Ensure CookieUtils is defined
        if (typeof CookieUtils === 'undefined') {
            CookieUtils = {
                set(name, value, days = 365) {
                    const expires = new Date(Date.now() + days * 86400000).toUTCString();
                    document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/`;
                },

                get(name) {
                    const cookies = document.cookie.split(';').reduce((acc, cookie) => {
                        const [key, value] = cookie.split('=').map(c => c.trim());
                        acc[key] = value;
                        return acc;
                    }, {});
                    return cookies[name] ? decodeURIComponent(cookies[name]) : null;
                }
            };
        }

        // Load the first track
        if (!CookieUtils.get('audioPlayerState')) {
            audioController.loadTrack(0);
        }
        currentTrackIndex = 0;
        if (typeof savedState === 'undefined') {
            var savedState = CookieUtils.get('audioPlayerState') ? JSON.parse(CookieUtils.get('audioPlayerState')) : null;
        } else {
            savedState = CookieUtils.get('audioPlayerState') ? JSON.parse(CookieUtils.get('audioPlayerState')) : null;
        }
        // Check if the cookie exists and apply volume and playback rate
        if (savedState) {
            audioController.audio.volume = savedState.volume ?? 1;
            audioController.audio.playbackRate = savedState.playbackRate || 1;
            uiController.controls.volumeSlider.value = savedState.volume ?? 1;
            uiController.controls.speedSelector.value = savedState.playbackRate || 1;
            if (savedState.muted === true) {
                document.querySelector(".volume-icon").classList.toggle("hidden", savedState.muted);
                document.querySelector(".mute-icon").classList.toggle("hidden", !savedState.muted);
            }
            currentTrackIndex = savedState.currentTrackIndex;

            const volumeSlider = document.querySelector('input[type="range"].volume-slider');

        // Function to update the gradient for WebKit browsers
        const updateGradient = () => {
            // Calculate the percentage of the filled part based on the slider value
            const value = (volumeSlider.value - volumeSlider.min) / (volumeSlider.max - volumeSlider.min) * 100;
            volumeSlider.style.setProperty('--gradient-progress', `${value}%`);
        };

        // Update the gradient when the input value changes
        volumeSlider.addEventListener('input', () => {
            updateGradient();
        });

        // Initialize the gradient
        updateGradient();
        }

        // Update track info and render playlist
        uiController.updateTrackInfo(currentTrackIndex);
        uiController.renderPlaylist();

        $(`#mini-maxi`)
            .unbind()
            .on("click", function () {
                let icon = $(this).find('i');
            if(icon.hasClass('fa-caret-down')) {
                $(`.maximized`).addClass('d-none');
                $(`.audio-player`).addClass('audio-player-mini');
                $(`.controls`).addClass('mt-0');
                $(`#player-arg-control > button`).addClass('mini');
                $(`#player-arg-control`).addClass('mini-p-arg-c');
                $(`.main-controls`).addClass('p-0');
                CookieUtils.set('playerState', 'minimized');
            } else {
                $(`.maximized`).removeClass('d-none');
                $(`.audio-player`).removeClass('audio-player-mini');
                $(`.controls`).removeClass('mt-0');
                $(`#player-arg-control > button`).removeClass('mini');
                $(`#player-arg-control`).removeClass('mini-p-arg-c');
                $(`.main-controls`).removeClass('p-0');
                CookieUtils.set('playerState', 'maximized');
            }
            icon.toggleClass("fa-caret-down fa-caret-up")
        });


        // Restore state from cookies on page load
        $(document).ready(function () {
            // Get the saved player state from cookies
            const playerState = CookieUtils.get('playerState');
            console.log(playerState);
            // Restore the player state
            if (playerState === 'minimized') {
                // Minimize the player
                $(`.maximized`).addClass('d-none');
                $(`.audio-player`).addClass('audio-player-mini');
                $(`.controls`).addClass('mt-0');
                $(`#player-arg-control > button`).addClass('mini');
                $(`#player-arg-control`).addClass('mini-p-arg-c');
                $(`.main-controls`).addClass('p-0');
                $(`#mini-maxi i`).removeClass('fa-caret-down').addClass('fa-caret-up');
            } else {
                // Maximize the player (default state)
                $(`.maximized`).removeClass('d-none');
                $(`.audio-player`).removeClass('audio-player-mini');
                $(`.controls`).removeClass('mt-0');
                $(`#player-arg-control > button`).removeClass('mini');
                $(`#player-arg-control`).removeClass('mini-p-arg-c');
                $(`.main-controls`).removeClass('p-0');
                $(`#mini-maxi i`).removeClass('fa-caret-up').addClass('fa-caret-down');
            }
        });


    </script>
    <?php
    return ob_get_clean();
}

if (isset($_SESSION['audio_files'])) {
    // Render the player and return the HTML
    echo render_player($_SESSION['audio_files']);
}
?>