class AudioController {
    constructor() {
        this.audio = new Audio();
        this.currentTrackIndex = 0;
        this.isPlaying = false;

        // Restore state from cookies
        this.restoreState();

        this.audio.addEventListener("ended", () => this.onTrackEnd());
        this.audio.addEventListener("timeupdate", () => this.onTimeUpdate());
        this.audio.addEventListener("loadedmetadata", () => this.onTrackLoad());
    }

    saveState() {
        const state = {
            currentTrackIndex: this.currentTrackIndex,
            currentTime: this.audio.currentTime,
            volume: this.audio.volume,  // Ensure volume is saved
            muted: this.audio.muted,
            playbackRate: this.audio.playbackRate,  // Ensure playback rate is saved
            isPlaying: this.isPlaying,
        };
        document.cookie = `audioPlayerState=${encodeURIComponent(
        JSON.stringify(state)
        )};path=/;max-age=31536000`; // Save state for 1 year
    }

    restoreState() {
        const cookies = document.cookie.split(";").reduce((acc, cookie) => {
            const [key, value] = cookie.split("=").map((c) => c.trim());
            acc[key] = value;
            return acc;
        }, {});
        const state = cookies.audioPlayerState
            ? JSON.parse(decodeURIComponent(cookies.audioPlayerState))
            : null;
        if (state) {
            this.currentTrackIndex = state.currentTrackIndex || 0;
            this.audio.currentTime = state.currentTime || 0;
            this.audio.volume = state.volume ?? 1;
            this.audio.muted = state.muted || false;
            this.audio.playbackRate = state.playbackRate || 1;
            this.isPlaying = state.isPlaying || false; // Restore the playing state
    
            // Restore the playing track
            this.loadTrack(this.currentTrackIndex);
    
            // Automatically play the track if it was playing before
            if (this.isPlaying) {
                this.play();
            }
        }
    }

    loadTrack(index) {
        this.currentTrackIndex = index;
        this.audio.src = CONFIG.tracks[index].url;
        this.audio.load();
        this.saveState();
        document.dispatchEvent(
            new CustomEvent("trackchange", {
                detail: { track: CONFIG.tracks[index] },
            })
        );
    }

    play() {
        this.audio.play();
        this.isPlaying = true;
        this.saveState();
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.saveState();
    }

    togglePlay() {
        if (this.isPlaying) {
        this.pause();
        } else {
        this.play();
        }
    }

    setVolume(value) {
        this.audio.volume = value;
        this.saveState();
    }

    setMuted(muted) {
        this.audio.muted = muted;
        this.saveState();
    }

    setPlaybackRate(rate) {
        this.audio.playbackRate = rate;
        this.saveState();
    }

    seek(time) {
        this.audio.currentTime = time;
        this.saveState();
    }

    nextTrack() {
        const nextIndex = (this.currentTrackIndex + 1) % CONFIG.tracks.length;
        this.loadTrack(nextIndex);
        if (this.isPlaying) this.play();
    }

    previousTrack() {
        const prevIndex =
        this.currentTrackIndex === 0
            ? CONFIG.tracks.length - 1
            : this.currentTrackIndex - 1;
        this.loadTrack(prevIndex);
        if (this.isPlaying) this.play();
    }

    onTrackEnd() {
        this.nextTrack();
    }

    onTimeUpdate() {
        document.dispatchEvent(
            new CustomEvent("timeupdate", {
                detail: {
                    currentTime: this.audio.currentTime,
                    duration: this.audio.duration,
                },
            })
        );
    }

    onTrackLoad() {
        document.dispatchEvent(
            new CustomEvent("trackload", {
                detail: { duration: this.audio.duration },
            })
        );
    }
}
