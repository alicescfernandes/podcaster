@php global $fm; @endphp

 <div class="podcaster-jumbotron">
     <div class="jumbotron-content">
         <div class="podcaster-jumbotron-image" style="background-image: url('{{$fm->get("playlists/{$playlist->folder}", $playlist->image_1x1)}}');"></div>
         <div class="podcaster-jumbotron-content">
             <h1 class="podcaster-jumbotron-h1">{{$playlist->title}}</h1>
             <h2 class="podcaster-jumbotron-h2">{{$user->full_name}}</h2>
             <span class="podcaster-description">
                 <p>{{$playlist->description}}</p>
             </span>

             <span class="player-controls">
                 {{--<button class="player-play player-control-white">
                     <span class="podcaster-icon">
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 55.564 64.401">
                             <path d="M139.439-2950.25,94.167-2977.3a5.146,5.146,0,0,0-7.785,4.417v54.093a5.145,5.145,0,0,0,7.785,4.417l45.273-27.046A5.145,5.145,0,0,0,139.439-2950.25Z" transform="translate(-86.382 2978.034)" /></svg>
                     </span> Play</button>--}}
                 <span class="player-duration white">{{count($episodes)}} {{ count($episodes) >= 1 ? "episodes" : "episode" }}</span>
             </span>
             <span class="hashtags">
                 <p>
                 @foreach($tags as $t)
                     <a href="//{{HTTP_HOST}}/tags/{{$t->tag}}">#{{$t->tag}}</a>
                 @endforeach
                 </p>
             </span>
         </div>

     </div>
   
 </div>
