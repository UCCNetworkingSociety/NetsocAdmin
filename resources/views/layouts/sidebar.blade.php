<aside class="">
	<div class="container"><a href="#" data-activates="nav-mobile" class="button-collapse top-nav full hide-on-large-only"><i class="mdi-navigation-menu"></i></a></div>
	<ul id="nav-mobile" class="side-nav fixed">
        <li class="logo">
        	<a id="logo-container" href="{{ URL::route('home') }}" class="brand-logo">
            	<img src="{{URL::to('/')}}/images/logo.png" />
            </a>
        </li>
        <li id="home"><a href="{{URL::to('/')}}" class="waves-effect waves-red">Home</a></li>
        <li id="mysql"><a href="{{ URL::route('manage/mysql') }}" class="waves-effect waves-red">MySQL Databases</a></li>
        <li id="account"><a href="{{ URL::route('manage/account') }}" class="waves-effect waves-red">Account</a></li>
        <li id="wordpress"><a href="{{ URL::route('manage/wordpress') }}" class="waves-effect waves-red">Install Wordpress</a></li>
        <li id="backups"><a href="{{ URL::route('manage/backups') }}" class="waves-effect waves-red">Backups</a></li>
        
        <li id="ssh"><a href="{{ URL::route('static/ssh') }}" class="waves-effect waves-red">How To Login</a></li>
        <li id="irc"><a href="{{ URL::route('static/irc') }}" class="waves-effect waves-red">How To IRC</a></li>

        <li id="logout"><a href="{{ URL::route('logout') }}" class="waves-effect waves-red">Logout</a></li>
        {{-- <li class="no-padding">
          <ul class="collapsible collapsible-accordion">
            <li class="bold active"><a class="collapsible-header active waves-effect waves-teal">CSS</a>
              <div class="collapsible-body" style="display: block;">
                <ul>
                  <li><a href="color.html">Color</a></li>
                  <li><a href="grid.html">Grid</a></li>
                  <li><a href="helpers.html">Helpers</a></li>
                  <li><a href="media-css.html">Media</a></li>
                  <li><a href="sass.html">Sass</a></li>
                  <li><a href="shadow.html">Shadow</a></li>
                  <li><a href="table.html">Table</a></li>
                  <li><a href="typography.html">Typography</a></li>
                </ul>
              </div>
            </li>
            <li class="bold"><a class="collapsible-header  waves-effect waves-teal">Components</a>
              <div class="collapsible-body" style="">
                <ul>
                  <li><a href="badges.html">Badges</a></li>
                  <li><a href="buttons.html">Buttons</a></li>
                  <li><a href="breadcrumbs.html">Breadcrumbs</a></li>
                  <li><a href="cards.html">Cards</a></li>
                  <li><a href="chips.html">Chips</a></li>
                  <li><a href="collections.html">Collections</a></li>
                  <li><a href="footer.html">Footer</a></li>
                  <li><a href="forms.html">Forms</a></li>
                  <li><a href="icons.html">Icons</a></li>
                  <li><a href="navbar.html">Navbar</a></li>
                  <li><a href="pagination.html">Pagination</a></li>
                  <li><a href="preloader.html">Preloader</a></li>
                </ul>
              </div>
            </li>
            <li class="bold"><a class="collapsible-header  waves-effect waves-teal">JavaScript</a>
              <div class="collapsible-body" style="">
                <ul>
                  <li><a href="collapsible.html">Collapsible</a></li>
                  <li><a href="dialogs.html">Dialogs</a></li>
                  <li><a href="dropdown.html">Dropdown</a></li>
                  <li><a href="media.html">Media</a></li>
                  <li><a href="modals.html">Modals</a></li>
                  <li><a href="parallax.html">Parallax</a></li>
                  <li><a href="pushpin.html">Pushpin</a></li>
                  <li><a href="scrollfire.html">ScrollFire</a></li>
                  <li><a href="scrollspy.html">Scrollspy</a></li>
                  <li><a href="side-nav.html">SideNav</a></li>
                  <li><a href="tabs.html">Tabs</a></li>
                  <li><a href="transitions.html">Transitions</a></li>
                  <li><a href="waves.html">Waves</a></li>
                </ul>
              </div>
            </li>
          </ul>
        </li>
        <li class="bold"><a href="http://materializecss.com/mobile.html" class="waves-effect waves-teal">Mobile</a></li>
        <li class="bold"><a href="showcase.html" class="waves-effect waves-teal">Showcase</a></li> --}}
    </ul>

</aside>