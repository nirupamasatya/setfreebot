<?php function mo_openid_display_feedback_form(){
    if ( 'plugins.php' != basename($_SERVER['PHP_SELF']) ) {
        return;
    }
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'wp-pointer' );
    wp_enqueue_script( 'utils' );
    wp_enqueue_style( 'mo_openid_plugins_page_style', plugins_url( 'includes/css/mo_openid_feedback.css', __FILE__ ) );
    ?>

    </head>
    <body>

    <!-- The Modal -->
    <div id="myModal" class="mo_openid_modal1">

        <!-- Modal content -->
        <div class="mo_openid_modal1-content">
            <span class="mo_openid_close">&times;</span>
            <h3>What Happened? </h3>
            <form name="f" method="post" action="" id="mo_openid_feedback">
                <input type="hidden" name="mo_openid_feedback" value="mo_openid_feedback"/>
                <div>
                    <p style="margin-left:2%">
                        <?php
                        $deactivate_reasons = array(
                            "Not Working",
                            "Not Receiving OTP During Registration",
                            "Does not have the features I am looking for",
                            "Does not support login with app I am looking for",
                            "Confusing Interface",
                            "Bugs in the plugin",
                            "I dont want to register",
                            "Other Reasons:"
                        );

                        foreach ( $deactivate_reasons as $deactivate_reasons ) {?>

                    <div  class="radio" style="padding:1px;margin-left:2%">
                        <label style="font-weight:normal;font-size:14.6px" for="<?php echo $deactivate_reasons; ?>">
                            <input type="radio" name="deactivate_plugin" value="<?php echo $deactivate_reasons;?>" required >
                            <?php echo $deactivate_reasons;?></label>
                    </div>


                    <?php } ?>
                    <br>

                    <textarea id="mo_openid_query_feedback" name="mo_openid_query_feedback"  rows="4" style="margin-left:2%" cols="50" placeholder="Write your query here"></textarea>
                    <br><br>
                    <div class="mo_openid_modal-footer" >
                        <input type="submit" name="mo_openid_feedback_submit" class="button button-primary button-large" value="Submit" />
                        <a  name="mo_openid_option" value="mo_openid_skip_feedback" style="float:right" onclick="mo_openid_skip_feedback()">Skip Now</a>
                    </div>
                </div>
            </form>
            <form name="f" method="post" action="" id="mo_openid_feedback_form_close">
                <input type="hidden" name="mo_openid_option" value="mo_openid_skip_feedback"/>
            </form>

        </div>

    </div>

    <script>
    function mo_openid_skip_feedback(){
        jQuery('#mo_openid_feedback_form_close').submit();
    }
        jQuery('a[aria-label="Deactivate Social Login, Social Sharing by miniOrange"]').click(function(){
// Get the mo_openid_modal
            <?php if(!get_option('mo_openid_feedback_form')){ ?>
            var mo_openid_modal = document.getElementById('myModal');

// Get the button that opens the mo_openid_modal
            var btn = document.getElementById("myBtn");

// Get the <span> element that closes the mo_openid_modal
            var span = document.getElementsByClassName("mo_openid_close")[0];

// When the user clicks the button, open the mo_openid_modal

            mo_openid_modal.style.display = "block";


            jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                var reason= jQuery(this).val();
                jQuery('#mo_openid_query_feedback').removeAttr('required')

                if(reason=='Facing issues During Registration'){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Can you please describe the issue in detail?");
                }else if(reason=="Does not have the features I'm looking for"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Let us know what feature are you looking for");
                }else if(reason=="Other Reasons:"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Can you let us know the reason for deactivation");
                    jQuery('#mo_openid_query_feedback').prop('required',true);

                }else if(reason=="Not Receiving OTP During Registration"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Can you please describe the issue in detail?");

                }else if(reason=="Bugs in the plugin"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Can you please let us know about the bug in detail?");

                }else if(reason=="Does not support login with app I am looking for"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Let us know which App are you looking for");

                }else if(reason=="Confusing Interface"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Finding it confusing? let us know so that we can improve the interface");

                }else if(reason=="Not Working"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "Can you please describe what is not working?");

                }
                else if(reason=="I dont want to register"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "");

                }else if(reason=="Login Credentials Not Working"){
                    jQuery('#mo_openid_query_feedback').attr("placeholder", "This is not a major issue please contact info@miniorange.com to get your issue resolved.");

                }
            });




            // When the user clicks on <span> (x), mo_openid_close the mo_openid_modal
            span.onclick = function() {
                mo_openid_modal.style.display = "none";
                jQuery('#mo_openid_feedback_form_close').submit();
            }

            // When the user clicks anywhere outside of the mo_openid_modal, mo_openid_close it
            window.onclick = function(event) {
                if (event.target == mo_openid_modal) {
                    mo_openid_modal.style.display = "none";
                }
            }
            return false;
            <?php } ?>
        });
    </script><?php
    }
    ?>