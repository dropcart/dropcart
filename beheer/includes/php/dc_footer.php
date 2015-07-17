</div><!-- /col -->
</div> <!-- /.row off-canvas -->
</div><!-- /container -->
<script src="<?php echo SITE_URL?>/beheer/includes/script/jquery.touchSwipe.min.js"></script>
<script>
    $(function(){

        var swipeDistanceTrigger = 80;

        $(document).swipe( {
            //Generic swipe handler for all directions
            swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
//                alert("You swiped " + direction );

                if( direction == "right" && distance >= swipeDistanceTrigger){
                    toggleMenu('show');
                }
                else if(direction == "left" && distance >= swipeDistanceTrigger){
                    toggleMenu('hide');
                }

            }
        });


        /* Whenever the window width changes, calculate the new min-height */
        $(window).resize( function(){
            setMinHeightMainContent();
        });

        /* Sets the min height of the main-content div */
        function setMinHeightMainContent() {
            var minHeight = $(window).outerHeight() - $('#top-bar').outerHeight() + 'px';
            $('.main-content').css('min-height', minHeight);
        }

        /* initiate min height */
        setMinHeightMainContent();

        $('[data-toggle="offcanvas"]').click(function(){
            toggleMenu();
        });

        $(document).on('click', '.dark-overlay', function(){
            toggleMenu('hide');
        });

        function toggleMenu(action){
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
    });
</script>
</body>
</html>