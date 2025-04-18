class UIController {
    constructor(audioController) {
        this.controls = new ControlsController(audioController);
        this.progress = new ProgressController(audioController);
        this.playlist = new PlaylistController(audioController);

        // Automatically update the play button when the track is loaded
        document.addEventListener("trackload", () => {
            this.controls.updatePlayButton();
        });
    }

    updateTrackInfo(index = 0) {
        this.playlist.updateTrackInfo(CONFIG.tracks[index]);
    }

    renderPlaylist() {
        this.playlist.renderPlaylist();
    }
}