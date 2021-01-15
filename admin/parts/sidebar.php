<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading"></li>
                <?php if ($role->can_list_users) { ?>
                    <li>
                        <a href="?p=users" class="">
                            <i class="metismenu-icon pe-7s-rocket"></i>
                            Manage Users
                        </a>
                    </li>
                <?php } ?>

                <?php if ($role->can_list_playlist) { ?>

                    <li>
                        <a href="?p=playlists" class="">
                            <i class="metismenu-icon pe-7s-rocket"></i>
                            Manage Playlists
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role->can_list_episodes) { ?>

                    <li>
                        <a href="?p=episodes" class="">
                            <i class="metismenu-icon pe-7s-rocket"></i>
                            Manage Episodes
                        </a>
                    </li>
                <?php } ?>

            </ul>
        </div>
    </div>
</div>