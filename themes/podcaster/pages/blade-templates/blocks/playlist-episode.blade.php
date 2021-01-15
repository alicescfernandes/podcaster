@php global $fm; global $s3 @endphp
<div class="playlist-episode">
    <div>
        <div class="playlist-episode-image">
            <img src="{{$fm->get("episodes/{$episode->folder}", $episode->image_1x1)}}">
        </div>
        <div class="playlist-episode-content-title-container">
        @if($show_loop==true)
            <p class="playlist-episode-content-index">episode {{$loop+1}}</p>
        @endif
            <p class="playlist-episode-content-title">{{$episode->short_title}}</p>

        </div>
    </div>
    <div class="playlist-episode-content">
        <p class="playlist-episode-content-desc">{{$episode->description}}</p>
    </div>
    <div class="episode-actions">
        <span class="player-controls">
            <button data-src="{{$s3->getObjectURL("episodes/{$episode->folder}", $episode->file_high_quality)}}"
                class="player-play player-control-white">
                <span class="podcaster-icon podcaster-play">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 55.564 64.401">
                        <path
                            d="M139.439-2950.25,94.167-2977.3a5.146,5.146,0,0,0-7.785,4.417v54.093a5.145,5.145,0,0,0,7.785,4.417l45.273-27.046A5.145,5.145,0,0,0,139.439-2950.25Z"
                            transform="translate(-86.382 2978.034)" /></svg>
                    Play
                </span>
                <span class="podcaster-icon podcaster-stop">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48.22" height="60.031"
                        viewBox="0 0 48.22 60.031">
                        <g transform="translate(-23.453 -272)">
                            <rect width="15.22" height="60.031" rx="7.61" transform="translate(23.453 272)" />
                            <rect width="15.22" height="60.031" rx="7.61" transform="translate(56.453 272)" />
                        </g>
                    </svg>
                    Pause</span>
            </button>

        </span>
    </div>
</div>
