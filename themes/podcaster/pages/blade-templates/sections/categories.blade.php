
<div class="podcaster-categories-section podcaster-section">
    <h1>Categories</h1>

    <div class="podcaster-categories uk-child-width-expand@s" uk-grid>
        @foreach($tags as $t)
        @if($t == null)
            <p>No tags are available</p>
        @else
             <div class="podcaster-category gradient-{{($loop->index % 4) + 1}} ">
                <a href="//{{HTTP_HOST}}/tags/{{$t->tag}}">
                    <p>#{{$t->tag}}</p>
                </a>
            </div>
        @endif
        @endforeach

    </div>
</div>
