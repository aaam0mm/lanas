$(document).ready(function() {

  // audio
  $(`[aria-controls="collapseAudio"]`).on("click", function () {
    $(this).find("i").toggleClass("fa-caret-down fa-caret-up");
  });

  $(`#mini-maxi`)
    .unbind()
    .on("click", function () {
      if ($(this).parent().hasClass("max-top")) {
        $(`.mini-none`).addClass("d-none");
      }
      $(this)
        .find("i")
        .toggleClass("fa-caret-down fa-caret-up")
        .parents("#player-arg-control")
        .toggleClass("max-top");
      $(`.controled-mini-maxi`).toggleClass("d-none");
      $(`#audio-player`).toggleClass("col-md-3 col-md-4");
      // $(`#pauseButton`).click();
    });

  $(`#close-player`)
    .unbind()
    .on("click", function () {
      $.ajax({
        url: "/player.php?action=close",
        success: function (response) {
          if (response.match(/success/)) {
            $(`#audio-player`).remove();
            $(`.closed`).remove();
          } else {
            console.log("Error close player");
          }
        },
      });
    });

  $(`#closeSpeedContainer`)
    .unbind()
    .on("click", function () {
      $(`.mini-none`).addClass("d-none");
    });

  // Initialize the Howler.js sound object
  // Ensure variables are not redeclared
  let currentIndex = parseInt(getCookie("player_currentIndex")) || 0;
  let isShuffled = false;
  let playlist = document.querySelectorAll("#playlist li");
  let sound;
  let progressInterval;

  const speedButton = document.getElementById("speedButton");
  const speedContainer = document.getElementById("speedContainer");
  const speedSlider = document.getElementById("speedSlider");
  const speedValue = document.getElementById("speedValue");

  let isMuted = getCookie("player_isMuted") === "true";
  const muteButton = document.getElementById("mute");
  const volumeSlider = document.getElementById("volumeSlider");

  let isPlaying = false; // Track the play state
  const playButton = document.getElementById("playButton");
  const pauseButton = document.getElementById("pauseButton");

  // Helper function to set a cookie
  function setCookie(name, value, days = 365) {
    let expires = "";
    if (days) {
      const date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  // Helper function to get a cookie
  function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  // Handle speed button click to show or hide speed slider
  speedButton.addEventListener("click", function () {
    const isVisible = speedContainer.classList.contains("d-none");
    speedContainer.classList.toggle("d-none", !isVisible);
  });

  // Update sound speed when speed slider changes
  speedSlider.addEventListener("input", function () {
    let newSpeed = this.value;

    // Change the playback rate (speed) of the sound
    if (sound) {
      sound.rate(newSpeed); // Set playback rate
    }

    // Update the displayed speed value
    speedValue.innerText = `${newSpeed}x`;

    // Save the speed in cookies
    setCookie("player_speed", newSpeed);
  });

  // Handle mute/unmute toggle
  muteButton.addEventListener("click", function () {
    if (isMuted) {
      sound.mute(false); // Unmute the sound
      muteButton.innerHTML = '<i class="fas fa-volume-up"></i>'; // Change to volume up icon
    } else {
      sound.mute(true); // Mute the sound
      muteButton.innerHTML = '<i class="fas fa-volume-off"></i>'; // Change to volume off icon
    }
    isMuted = !isMuted;

    // Save the mute state in cookies
    setCookie("player_isMuted", isMuted);
  });

  // Update the volume slider and its background color
  function updateSliderBackground(volumeLevel) {
    const percentage = volumeLevel * 100;
    volumeSlider.style.background = `linear-gradient(to left, #000000 ${percentage}%, #ddd ${percentage}%)`;
  }

  // Initialize the volume slider and update its background color on page load
  let initialVolume = getCookie("player_volume") || 1;
  volumeSlider.value = initialVolume;
  updateSliderBackground(volumeSlider.value);

  volumeSlider.addEventListener("input", function () {
    const volumeLevel = this.value;

    // Update the sound volume if it"s not muted
    if (!isMuted) {
      sound.volume(volumeLevel);
    }

    // Update the background color of the slider based on volume
    updateSliderBackground(volumeLevel);

    // Save the volume level in cookies
    setCookie("player_volume", volumeLevel);
  });

  function loadTrack(index) {
    let selectedTrack = playlist[index];
    let src = selectedTrack.getAttribute("data-src");
    // Stop any previous sound
    if (sound) sound.stop();

    // Create a new Howl sound object
    sound = new Howl({
      src: [src],
      volume: document.getElementById("volumeSlider").value,
      rate: speedSlider.value, // Set the rate based on the speed slider value
      onplay: updateTrackInfo,
      onend: nextTrack,
    });

    // Play the new track
    // sound.play();

    // Update track info
    document.getElementById("trackName").innerText = selectedTrack.innerText;

    // Update current index
    currentIndex = index;

    // Remove "text-warning" class from all tracks
    playlist.forEach((track) => {
      track.classList.remove("text-warning");
    });

    // Add "text-warning" class to the current track
    selectedTrack.classList.add("text-warning");

    // Update the audio duration in the dropdown
    sound.once("load", function () {
      let duration = sound.duration();
      document.getElementById("d-duration").innerText = formatTime(duration);
    });
  }

  function updateTrackInfo() {
    document.getElementById("duration").innerText = formatTime(sound.duration());
    progressInterval = setInterval(updateProgress, 1000);
  }

  // Utility function to format time
  function formatTime(secs) {
    let minutes = Math.floor(secs / 60);
    let seconds = Math.floor(secs % 60);
    return `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
  }

  function playTrack() {
    if (sound) {
      sound.play();
      isPlaying = true;
      playButton.style.display = "none";
      pauseButton.style.display = "inline";
    }
  }

  function pauseTrack() {
    if (sound) {
      sound.pause();
      isPlaying = false;
      pauseButton.style.display = "none";
      playButton.style.display = "inline";
    }
  }

  function nextTrack() {
    currentIndex = isShuffled
      ? Math.floor(Math.random() * playlist.length)
      : (currentIndex + 1) % playlist.length;
    loadTrack(currentIndex);
    playTrack();
  }

  function prevTrack() {
    currentIndex = currentIndex > 0 ? currentIndex - 1 : playlist.length - 1;
    loadTrack(currentIndex);
    playTrack();
  }

  // function shuffleTracks() {
  //     isShuffled = !isShuffled;
  //     document.getElementById("shuffleButton").classList.toggle("active");
  // }

  // Load saved settings on page load
  window.addEventListener("DOMContentLoaded", () => {
    const savedSpeed = getCookie("player_speed") || 1;
    speedSlider.value = savedSpeed;
    speedValue.innerText = `${savedSpeed}x`;

    const savedMuteState = getCookie("player_isMuted") === "true";
    if (savedMuteState) {
      isMuted = true;
      muteButton.innerHTML = '<i class="fas fa-volume-off"></i>';
    } else {
      isMuted = false;
      muteButton.innerHTML = '<i class="fas fa-volume-up"></i>';
    }

    loadTrack(currentIndex);
  });

  // Event Listeners for controls
  playButton.addEventListener("click", () => {
    if (!isPlaying) {
      playTrack();
    }
  });

  pauseButton.addEventListener("click", () => {
    if (isPlaying) {
      pauseTrack();
    }
  });

  document.getElementById("nextButton").addEventListener("click", nextTrack);
  document.getElementById("prevButton").addEventListener("click", prevTrack);
  // document.getElementById("shuffleButton").addEventListener("click", shuffleTracks);

  // Click event on playlist
  playlist.forEach((track, index) => {
    track.addEventListener("click", () => {
      loadTrack(index);
      playTrack();
    });
  });

  // Load the first track on page load
  loadTrack(currentIndex);

  const progressBar = document.getElementById("progress-bar");
  const progressContainer = document.getElementById("progress-container");

  // Function to update the progress bar
  function updateProgress() {
    if ($(`#audio-player`).length > 0) {
      if (sound) {
        let currentTime = sound.seek(); // Get the current time in seconds
        let duration = sound.duration(); // Get the total duration in seconds
        let progressPercent = (currentTime / duration) * 100; // Calculate progress percentage
        progressBar.style.width = `${progressPercent}%`; // Update the progress bar width
        document.getElementById("currentTime").innerText =
          formatTime(currentTime); // Update current time display
      }
    }
  }

  // Function to handle click on the progress container
  function seekAudio(event) {
    const progressContainerWidth = progressContainer.offsetWidth; // Get the total width of the progress bar container
    const clickX = event.offsetX; // Get the horizontal position of the click
    const duration = sound.duration(); // Get the total duration of the audio

    // Calculate the new time to seek based on click position
    const newTime = (clickX / progressContainerWidth) * duration;

    // Set the audio to the new time
    sound.seek(newTime);
  }

  // Update progress every second
  progressInterval = setInterval(updateProgress, 1000);

  // Add event listener for clicking on the progress bar
  progressContainer.addEventListener("click", seekAudio);

});