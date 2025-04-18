<?php
    include 'init.php';
    $action = isset($_GET['action']) ? $_GET['action'] : 'default';
    function player($audio_files) {
        if(is_array($audio_files)) {
            $playList = '';
            foreach ($audio_files as $index => $audio): 
                $playList.= '
                    <li data-src="'. $audio["file_url"] .'">
                        ' . htmlspecialchars($index + 1 . ". " . $audio["track_name"]) . '
                    </li>
                ';
                if ($index != count($audio_files) - 1) {
                    $playList.='<div class="border-bottom my-2"></div>' . PHP_EOL;
                }
                ?>
            <?php endforeach;
            return '
                <div id="audio-player" class="shadow col-sm-12 col-md-3">
                    <div id="player-arg-control" class="btn-group p-0 m-0">
                        <button id="close-player" class="btn btn-default bg-white rounded-0">
                            <i class="fas fa-times"></i>
                        </button>
                        <button id="mini-maxi" class="btn btn-default bg-white rounded-0">
                            <i class="fas fa-caret-down"></i>
                        </button>
                    </div>
                    <!-- speed range controll -->
                    <div id="speedContainer" class="position-absolute rounded-0 w-100 mini-none d-none" style="background: rgba(198, 200, 55, 0.5);border-radius: 16px;box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);backdrop-filter: blur(5px);-webkit-backdrop-filter: blur(5px);border: 1px solid rgba(198, 200, 55, 0.3);height:75px; top:0; left:0;display: flex;flex-direction: column;align-items: center;">
                        <input type="range" id="speedSlider" min="0.5" max="2" step="0.1" value="1" style="margin: 10px auto 5px;width: 83%;">
                        <span id="speedValue" style="background-color: #030303;padding: 3px;border-radius: 5px;color: #FFF;font-size: 14px;">1x</span>
                        <button id="closeSpeedContainer" class="btn btn-sm btn-danger rounded-0" style="position: absolute; left:0; top:0">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- Controls -->
                    <div id="controls">
                        <button id="nextButton"><i class="fas fa-fast-forward d-none controled-mini-maxi circled"></i></button>
                        <button id="playButton"><i class="fas fa-play-circle"></i></button>
                        <button id="pauseButton" style="display: none;"><i class="fas fa-pause-circle"></i></button>
                        <button id="prevButton"><i class="fas fa-fast-backward d-none controled-mini-maxi circled"></i></button>
                        <button id="speedButton"><i class="fas fa-clock d-none controled-mini-maxi"></i></button>
                        <!-- <button id="shuffleButton"><i class="fas fa-random d-none controled-mini-maxi"></i></button> -->
                        <input type="range" id="volumeSlider" min="0" max="1" step="0.1" value="0.5">
                        <button id="mute"><i class="fas fa-volume-up"></i></button> <!-- Default icon is "volume-up" -->
                        <span id="currentTime" class="d-none controled-mini-maxi">00:00</span><span class="d-none controled-mini-maxi"> / </span><span id="duration" class="d-none controled-mini-maxi">00:00</span>
                    </div>

                    <!-- Track Info -->
                    <div id="track-info" class="d-none controled-mini-maxi">
                        <p id="trackName">- اسم المقطع -</p>
                        <div id="progress-container" class="progress-container">
                            <div id="progress-bar" class="progress-bar"></div>
                        </div>
                        <!-- <p id="currentTime">0:00</p>
                        <p id="duration">0:00</p> -->
                    </div>

                    <!-- Playlist -->
                    <ul id="playlist" class="d-none controled-mini-maxi">
                        <!-- Example Tracks -->
                        '. $playList .'
                    </ul>
                </div>
                <script class="closed" src="'. siteurl() .'/assets/js/howler.min.js"></script>
                <script class="closed" src="'. siteurl() .'/assets/js/player.js"></script>
            ';
        } else {
            return false;
        }
    }
    if($action == 'default') {
        if (isset($_SESSION["audio_files"])): 
            echo player($_SESSION["audio_files"]);
        endif;
    } elseif(isset($_GET['action']) && $_GET['action'] == "close") {
        $status = "error";
        if(isset($_SESSION['audio_files'])) {
            $_SESSION['closed_list'] = $_SESSION['audio_files'];
            unset($_SESSION['audio_files']);
        }
        if(!isset($_SESSION['audio_files'])) {
            $status = "success";
        }
        echo $status;
    } elseif(isset($_GET['action']) && $_GET['action'] == "open") {
        $status = "error";
        if(!isset($_SESSION['audio_files'])) {
            if(isset($_SESSION['closed_list']) && count($_SESSION['closed_list']) > 0) {
                $_SESSION['audio_files'] = $_SESSION['closed_list'];
                unset($_SESSION['closed_list']);
            }
        }
        if(isset($_SESSION['audio_files'])) {
            $status = player($_SESSION['audio_files']);
        }
        echo $status;
    }
?>