class PlaylistController {
  constructor(audioController) {
    this.audioController = audioController;
    this.trackList = document.querySelector(".track-list");
    this.trackTitle = document.querySelector(".track-title");
    this.setupEventListeners();
  }

  setupEventListeners() {
    document.addEventListener("trackchange", (e) => {
      this.updateTrackInfo(e.detail.track);
    });
  }

  updateTrackInfo(track) {
    this.trackTitle.textContent = track.title;

    const items = this.trackList.querySelectorAll("li");
    items.forEach((item, index) => {
      item.classList.toggle(
        "active",
        index === this.audioController.currentTrackIndex
      );
    });
  }

  renderPlaylist() {
    this.trackList.innerHTML = "";
    CONFIG.tracks.forEach((track, index) => {
      let classActive = index === this.audioController.currentTrackIndex ? "active" : '';
      const li = Utils.createElement("li", classActive, track.title);
      li.addEventListener("click", () => {
        this.audioController.loadTrack(index);
        this.audioController.play();
        this.audioController.saveState();
      });
      this.trackList.appendChild(li);
    });
  }
}
