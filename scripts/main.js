    $(document).ready(function() {
        filter();
        $("#open-form-button").hide();
    });
    // Screen lock control
    var noSleep = new NoSleep();
    noSleep.enable(); // keep the screen on!
    var wakeLockEnabled = true;
    var toggleEl = document.querySelector("#togglekeep");
    toggleEl.addEventListener('click', function() {
        if (!wakeLockEnabled) {
          noSleep.enable(); // keep the screen on!
          wakeLockEnabled = true;
          toggleEl.title = "Wake Lock is enabled";
          toggleEl.style.color = "lightskyblue";
        } else {
          noSleep.disable(); // let the screen turn off.
          wakeLockEnabled = false;
          toggleEl.title = "Wake Lock is disabled";
          toggleEl.style.color = "darkslategray";
        }
    }, false);

