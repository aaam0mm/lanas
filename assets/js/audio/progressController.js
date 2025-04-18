class ProgressController {
  constructor(audioController) {
    this.audioController = audioController;
    this.isDragging = false;
    this.initElements();
    this.setupEventListeners();
  }

  initElements() {
    this.progressBar = document.querySelector(".audio-player .progress-bar");
    this.progress = document.querySelector(".audio-player .progress-bar .progress");
    this.currentTimeDisplay = document.querySelector(".current-time");
    this.durationDisplay = document.querySelector(".duration");
  }

  setupEventListeners() {
    this.progressBar.addEventListener("mousedown", (e) => {
      this.isDragging = true;
      this.updateFromEvent(e);
    });

    document.addEventListener("mousemove", (e) => {
      if (this.isDragging) {
        this.updateFromEvent(e);
      }
    });

    document.addEventListener("mouseup", () => {
      this.isDragging = false;
    });

    document.addEventListener("timeupdate", (e) => {
      if (!this.isDragging) {
        this.updateProgress(e.detail.currentTime, e.detail.duration);
      }
    });

    document.addEventListener("trackload", (e) => {
      this.durationDisplay.textContent = Utils.formatTime(e.detail.duration);
    });
  }

  updateFromEvent(e) {
    const rect = this.progressBar.getBoundingClientRect();
    const pos = Math.min(Math.max(0, (e.clientX - rect.left) / rect.width), 1);
    this.progress.style.width = `${pos * 100}%`;

    if (this.audioController.audio.duration) {
      const time = pos * this.audioController.audio.duration;
      this.audioController.seek(time);
      this.currentTimeDisplay.textContent = Utils.formatTime(time);
    }
  }

  updateProgress(currentTime, duration) {
      if (duration > 0) {
        let progressPercentage = (currentTime / duration) * 100;
        this.progress.style.width = `${progressPercentage}%`;
      }
      this.currentTimeDisplay.textContent = Utils.formatTime(currentTime);
      this.durationDisplay.textContent = Utils.formatTime(duration);
  }
}
