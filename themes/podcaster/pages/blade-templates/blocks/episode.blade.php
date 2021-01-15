@php global $fm; global $s3 @endphp

<div class="episode episode-{{$episode->visibilty}} uk-width-1-1@m uk-width-1-3@l uk-width-1-3@x1">
    <div class="episode-img uk-width-1-2"
        style="background-image: url('{{$fm->get("episodes/{$episode->folder}", $episode->image_1x1)}}');"></div>
    <div class="episode-content uk-width-1-2">
        <span class="episode-playlist">
            <a href="//{{HTTP_HOST}}/playlists/{{$episode->playlist->id}}">{{$episode->playlist->title}}</a>
        </span>
        <h2 class="episode-title">{{$episode->short_title}}</h2>
        <p class="episode-duration">{{$episode->duration}} minutos</p>
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

</div>