<div class="podcaster-categories-section podcaster-section">
    @if($show_loop == true)
        <h1>Episodes</h1>
    @endif
    <div class="podcaster-playlist-episodes podcaster-categories">
        @foreach($episodes as $p)
            @if(in_array($p->visibilty, $user_visibility))
                @include("blocks.playlist-episode", ["episode"=>$p, "loop"=>$loop->index, "show_loop"=>$show_loop])
            @endif
        @endforeach

     {{-- 

           @foreach($tags as $t)
             <div class="podcaster-category gradient-{{($loop->index % 4) + 1}} ">
                <a href="/tags/{{$t->tag}}">
                    <p>#{{$t->tag}}</p>
                </a>
            </div>
        @endforeach
     --}}

    </div>
</div>
