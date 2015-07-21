</div><!-- /col -->
</div> <!-- /.row off-canvas -->
</div><!-- /container -->
<script src="<?php echo SITE_URL?>/beheer/includes/script/jquery.touchSwipe.min.js"></script>
<script>
    $(function(){

        /* Distance in pixels that the user needs to swipe in order to trigger the menu */
        var swipeDistanceTrigger = 80;

        /*  Max-width in pixels for small screens.
            If the current window is larger than this, then the
            sidebar won't get triggerd
        */
        var breakPointSmall = 992;

        function init(){
           resizeContent();
        }

        function resizeContent(){
            // Set the minheight of the wrapper, and then execute callback function
            setMinHeightOffCanvasWrapper(
                setMinHeightMainContent
            );

        }
    if( isSmallScreen() ) {
        $(document).swipe({
            //Generic swipe handler for all directions
            swipeRight: function (event, direction, distance, duration, fingerCount, fingerData) {
//                alert("You swiped " + direction );


                if (distance >= swipeDistanceTrigger) {
                    toggleMenu('show');
                }
                return true;
            },

            swipeLeft: function (event, direction, distance, duration, fingerCount, fingerData) {
//                alert("You swiped " + direction );


                if (distance >= swipeDistanceTrigger) {
                    toggleMenu('hide');
                }
            }

        });
    }



        /* Whenever the window width changes, calculate the new min-height */
        $(window).resize( function(){

            resizeContent();
            /*
                If the current windows is too large on resize, the sidebar needs to be
                forced inactive.
            */
            if( !isSmallScreen() ){
                // 'true' = force the sidebar to close, ignoring screensize
                toggleMenu('hide', true);
            }

        });

        /* Makes sure that the content width takes op whole screen height */
        function setMinHeightMainContent(setHeight) {

            $('.main-content').css('min-height', setHeight);
        }

        function setMinHeightOffCanvasWrapper(callback){
            var minheight = $(document).outerHeight(true) - $('#top-bar').outerHeight(true);
            $('.offcanvas').css('min-height', minheight);


            if( typeof callback === "function"){
                callback(minheight);
            }
        }

        /* Get's full width of the window (including scrollbars) */
        function getWidth() {
            if (self.innerWidth) {
                return self.innerWidth;
            }
            else if (document.documentElement && document.documentElement.clientHeight){
                return document.documentElement.clientWidth;
            }
            else if (document.body) {
                return document.body.clientWidth;
            }
            return 0;
        }
        /* Get's full height of the window (including scrollbars) */
        function getHeight() {
            if (self.innerHeight) {
                return self.innerHeight;
            }

            if (document.documentElement && document.documentElement.clientHeight) {
                return document.documentElement.clientHeight;
            }

            if (document.body) {
                return document.body.clientHeight;
            }
        }



        $('[data-toggle="offcanvas"]').click(function(){
            toggleMenu();
        });

        $(document).on('click', '.dark-overlay', function(){
            toggleMenu('hide');
        });

        function isSmallScreen(){
            return getWidth() < breakPointSmall;
        }

        /**
         * Show or hide the sidebar menu
         *
         * @param {string} action - the action that the sidebar has to perform
         * There are 2 supported action: 'hide' and 'show'. If no action given,
         * the sidebar wil default to toggeling.
         *
         * @param {boolean} force - force toggleMenu() to ignore current screensize
         *
         * @return void
         */
        function toggleMenu(action, force){

           if( typeof force == "undefined"){ force = false; }

           if( !isSmallScreen() && !force ){
                return;
           }

            var nav = $('.offcanvas');

            if( typeof action === "undefined") {
                nav.toggleClass('active');

            }

            else if( action == "show" ){
                nav.addClass('active');
            }
            else if( action == "hide"){
                nav.removeClass('active');
            }

            /* toggle overlay */
            if( nav.hasClass('active')){
                $('.dark-overlay').removeClass('hidden');
            }
            else{
                $('.dark-overlay').addClass('hidden');
            }

        }

        init();
    });
</script>
</body>
</html>