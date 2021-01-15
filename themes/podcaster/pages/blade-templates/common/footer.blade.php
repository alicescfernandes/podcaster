<script src="//{{HTTP_HOST}}/themes/{{DEFAULT_THEME}}/assets/js/uikit.min.js"></script>
<script src='//{{HTTP_HOST}}/themes/{{DEFAULT_THEME}}/assets/js/mediaelement-and-player.js'></script>
<script>

var mediaElements = document.querySelector('#audio');
var player =  new MediaElementPlayer(mediaElements, {
    features: ['prevtrack', 'playpause', 'nexttrack', 'current', 'progress', 'duration', 'volume', 'playlist', 'shuffle', 'loop', 'fullscreen'] });

$('.player-control-white').on("click", function(){
  if($(this).hasClass("player-stop")){
    $(".player-stop").removeClass("player-stop")
    $(this).addClass("player-play")
    player.pause()
  }else{
    var src = $(this).attr("data-src");
    $(".player-stop").removeClass("player-stop")
    $(this).addClass("player-stop")
    $(this).removeClass("player-play")
    player.setSrc(src)
    player.play()
  }
})

</script>