<!DOCTYPE html>
<html lang="en">
<head>
    @include('common.header')
</head>
<body>
    <header>
        <a href="//{{HTTP_HOST}}"><img src="//{{HTTP_HOST}}/themes/{{DEFAULT_THEME}}/assets/img/logo.png"></a>
        @php 
        global $logged_user;
        @endphp

        @if($logged_user != false)
            <a class="user-stuff" href="//{{HTTP_HOST}}/admin/logout.php">  Logout</a>
            <a class="user-stuff" href="//{{HTTP_HOST}}/admin/"> <img src="<?= $_SESSION["avatar_url"]. "?".time();  ?>"/> Admin panel</a>
        @else
            <a class="user-stuff" href="//{{HTTP_HOST}}/admin/login.php">Login</a>
            <a class="user-stuff"  href="//{{HTTP_HOST}}/admin/register.php">Create Account</a>
        @endif
    </header>
    <div id="main" class="container">
        @yield('content')

    </div>

    @include('blocks.audio')
    <footer id="footer">
        @include('common.footer')
    </footer>
</body>
</html>
