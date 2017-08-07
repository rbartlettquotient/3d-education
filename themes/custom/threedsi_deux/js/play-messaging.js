/**
 * Project Play Smithsonian Integration
 *
 * @author Michael Staub <michael.staub@autodesk.com>
 *
 * @description This file handles the communication between the Drupal parent
 * page and the play scene iframe.
 */

(function() {
    // hard code the possible play domains for portability
    var playOrigins = [
        "https://play-staging.autodesk.com:8001",
        "https://play-staging.autodesk.com",
        "https://play.autodesk.com",
    ];

    var verifyParams = function(params) {
        // https://developers.google.com/analytics/devguides/collection/analyticsjs/events
        return params &&
            typeof params === "object" &&
            params.hitType &&
            params.eventCategory &&
            params.eventAction &&
            typeof params.hitType === "string" &&
            typeof params.eventCategory === "string" &&
            typeof params.eventAction === "string";
    };

    var onReceivePostMessage = function(event) {
        var data = event.data;
        if (playOrigins.indexOf(event.origin) >= 0 && data) {
            var eventName = data.Name;
            var eventMessage = data.Text;
            if (eventName === "googleAnalytics") {
                if (window.ga) {
                    try {
                        var gaParams = JSON.parse(eventMessage);
                    } catch(e) {
                        console.error("onReceivePostMessage: unable to parse json,", eventMessage);
                        return;
                    }
                    if (verifyParams(gaParams)) {
                        window.ga("send", "event", gaParams.eventCategory, gaParams.eventAction, gaParams.hitType);
                        if (gaParams.hitType === "LoadModel") {
                            // TODO prompt Drupal to open model selection dialog
                            // then Drupal sends an event into play to load new model
                            window.openPlayModelBrowser();
                        } else if (gaParams.hitType === "ShareOn") {
                            // TODO open Drupal share dialog
                            window.openPlayShareDialog();
                        }
                    } else {
                        console.error("onReceivePostMessage: invalid google analytics params");
                    }
                }
            }
        }
    };

    // listen for events coming from play
    window.addEventListener("message", onReceivePostMessage);

    /**
     * Sends a post message to the play iframe
     * @param  {String} iframeId the id of the iframe DOM node
     * @param  {Object} message the message object to send to Play
     * @param  {Boolean} [confirm] optional log a confirmation statement after sucessfull post
     */
    window.sendPostMessageToPlay = function(iframeId, message, confirm) {
        if (!iframeId || typeof iframeId !== "string") {
            console.error("window.sendPostMessageToPlay: first arg must be a string (iframe element id)");
            return;
        }
        if (!message || typeof message !== "object" || !message.name || typeof message.name !== "string") {
            console.error("window.sendPostMessageToPlay: second arg must be an object with a name property of type string");
            return;
        }
        var iframe = document.getElementById(iframeId);
        if (!iframe) {
            console.error("window.sendPostMessageToPlay: no iframe found with id:", iframeId);
            return;
        }
        for (var i = 0; i < playOrigins.length; i++) {
            try {
                var domain = playOrigins[i];
                iframe.contentWindow.postMessage(message, domain);
            } catch (e) {
                continue;
            }
            if (confirm) {
                console.log("window.sendPostMessageToPlay: success");
            }
            break;
        }
    }

    // attach listeners to dom nodes (local testing)
    if (document.location.hostname === "localhost") {
        document.addEventListener("DOMContentLoaded", function() {
            var postMessage1 = document.getElementById("post-message-1");
            postMessage1.addEventListener("click", function(e) {
                window.sendPostMessageToPlay(
                    "play-viewer-iframe",
                    { name: "spinCube" },
                    true
                );
            });
        });
    }
}());
