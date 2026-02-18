jQuery(document).ready(function($) {
    // Add event listener to the button
    const button = $('.ph-integrations-action');

    // Check if the URL has the parameter showPanel=true
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('showPanel') === 'true') {
        showSuretriggers();
    } else {
        $('.ph-integrations-wrapper').css('display', 'flex');
    }
    
    if (button.length && button.hasClass('suretriggers-active')) {
        button.on('click', function(event) {
            // Prevent the default action of the button
            event.preventDefault();
            showSuretriggers();
        });
    } else if (button.length) {
        button.on('click', function(event) {
            event.preventDefault();

            if (button.hasClass('suretriggers-not-installed') ) {
                // AJAX call to install and activate the plugin.
                button.text(PH_Integrations.installing_text );
                ajaxRequest('ph_install_and_activate', button );
            } else if ( button.hasClass('suretriggers-installed')) {
                // AJAX call to activate the plugin.
                button.text( PH_Integrations.activating_text );
                ajaxRequest('ph_activate_plugin', button );
            } else if ( button.hasClass('suretriggers-not-connected' ) ) {
                button.text( PH_Integrations.connecting_text );

                // Define the dimensions of the popup window
                const windowDimension = {
                    width: 800,
                    height: 700,
                };

                const positioning = {
                    left: (screen.width - windowDimension.width) / 2,
                    top: (screen.height - windowDimension.height) / 1.5,
                };

                const popupWindow = window.open(
                    PH_Integrations.suretriggers_url,
                    'SureTriggersPopup', 
                    `width=${windowDimension.width},height=${windowDimension.height},resizable,scrollbars,status,top=${positioning.top},left=${positioning.left}`
                );
                
                var iterations = 0;
                
                var suretriggersAuthInterval = setInterval(function() {
                    // Disable the button and update the CTA text
                    button.text(PH_Integrations.connecting_text).prop('disabled', true);
                
                    $.ajax({
                        url: PH_Integrations.ajax_url,  // Replace this with the actual URL to check authentication status.
                        type: 'POST',
                        data: {
                            action: 'check_suretriggers_connection',
                            nonce: PH_Integrations.nonce // Add nonce if needed for security
                        },
                        success: function(response) {
                            if (response.success) {
                                window.SureTriggersConfig = response.data; // Assuming response contains the relevant data.
                                popupWindow.close();
                                button.text(PH_Integrations.connected_text);
                                clearInterval(suretriggersAuthInterval);
                                reloadWithParameter('showPanel', 'true');
                            } else {
                                iterations++;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error checking authentication status:', error);
                        }
                    });
                
                    // Stop the loop after 2 minutes or if the popup is closed
                    if (iterations >= 240 || popupWindow.closed) {
                        if (!popupWindow.closed) {
                            popupWindow.close();
                        }
                        clearInterval(suretriggersAuthInterval);
                        button.text(PH_Integrations.connect_text).prop('disabled', false);  // Re-enable the button on failure.
                    }
                
                }, 1500); // Check every 500ms (half a second)
                           
                
            }
        });
    }

    function showSuretriggers() {
        const contentContainer = $('.ph-integrations-wrapper');
        const suretriggersEmbed = $('.ph-suretriggers-wrap');

        if ( contentContainer.length && suretriggersEmbed.length ) {
            // Hide the original content
            contentContainer.hide();

            // Show the SureTriggers embed
            suretriggersEmbed.css('display', 'flex');
        }
    }

    function reloadWithParameter(paramName, paramValue) {
        // Get the current URL
        let url = new URL(window.location.href);
    
        // Set or update the parameter
        url.searchParams.set(paramName, paramValue);
    
        // Reload the page with the new URL
        window.location.href = url.toString();
    }

    function ajaxRequest(action, button) {
        $.ajax({
            url: PH_Integrations.ajax_url, // WordPress AJAX URL.
            type: 'POST',
            data: {
                action: action,
                nonce: PH_Integrations.nonce 
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to the URL stored in the localized variable.
                    window.location.href = PH_Integrations.integration_url;
                } else {                   
                    errorMessage( action );
                }
            },
            error: function() {
                errorMessage( action );
            }
        });
    }

    function errorMessage( action ) {
        if( action === 'ph_activate_plugin' ) {
            alert( PH_Integrations.activation_failed );
            button.text( PH_Integrations.activate_text );
        } else if( action === 'ph_install_and_activate' ) {
            alert( PH_Integrations.installation_failed );
            button.text( PH_Integrations.install_text );
        }
    }
});