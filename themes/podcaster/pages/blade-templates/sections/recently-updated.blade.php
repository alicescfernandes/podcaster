<div class="podcaster-recently-updated podcaster-section ">
    <h1>Recently Updated</h1>
    <div uk-grid>
    @foreach($episodes as $episode)
        @if($episode != null and in_array($episode->visibilty, $user_visibility) && !empty($episode->file_high_quality))
            @include('blocks.episode', ["episode"=>$episode])
        @elseif( $episode == null)
            <p>No episodes are available</p>
        @endif

    @endforeach
    </div>
</div>
