class ControlsController {
  constructor(audioController) {
    this.audioController = audioController;
    this.initElements();
    this.setupEventListeners();
  }

  initElements() {
    this.playButton = document.querySelector(".play-button");
    this.playIcon = document.querySelector(".play-icon");
    this.pauseIcon = document.querySelector(".pause-icon");
    this.prevButton = document.querySelector(".prev-button");
    this.nextButton = document.querySelector(".next-button");
    this.volumeSlider = document.querySelector(".volume-slider");
    this.muteButton = document.querySelector(".mute-button");
    this.volumeIcon = document.querySelector(".volume-icon");
    this.muteIcon = document.querySelector(".mute-icon");
    this.speedSelector = document.querySelector(".speed-selector");
  }

  setupEventListeners() {
    this.playButton.addEventListener("click", () => {
      this.audioController.togglePlay();
      this.updatePlayButton();
      this.audioController.saveState(); // Save state when play/pause is toggled
    });

    this.prevButton.addEventListener("click", () => {
      this.audioController.previousTrack();
      this.audioController.saveState(); // Save state when track is changed
    });

    this.nextButton.addEventListener("click", () => {
      this.audioController.nextTrack();
      this.audioController.saveState(); // Save state when track is changed
    });

    this.volumeSlider.addEventListener("input", (e) => {
      this.audioController.setVolume(e.target.value);
      this.audioController.saveState(); // Save state when volume is changed
      this.updateVolumeIcon();
    });

    this.muteButton.addEventListener("click", () => {
      const isMuted = !this.audioController.audio.muted;
      this.audioController.setMuted(isMuted);
      this.audioController.saveState(); // Save state when mute is toggled
      this.updateVolumeIcon();
    });

    this.speedSelector.addEventListener("change", (e) => {
      this.audioController.setPlaybackRate(parseFloat(e.target.value));
      this.audioController.saveState(); // Save state when playback speed is changed
    });
  }

  updatePlayButton() {
    this.playIcon.classList.toggle("hidden", this.audioController.isPlaying);
    this.pauseIcon.classList.toggle("hidden", !this.audioController.isPlaying);
  }

  updateVolumeIcon() {
    const isMuted =
      this.audioController.audio.muted ||
      this.audioController.audio.volume === 0;
    this.volumeIcon.classList.toggle("hidden", isMuted);
    this.muteIcon.classList.toggle("hidden", !isMuted);
  }
}