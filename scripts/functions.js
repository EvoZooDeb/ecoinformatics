
    var myAuth = document.getElementById('sendLogIn');
    myAuth.addEventListener('click', auth, false);
    function auth(e) {
        e.preventDefault();
        let u = document.getElementById("username").value;
        let p = document.getElementById("password").value;
        $.ajax({
            type: "POST",
            headers: {
                "Authorization": "Basic " + btoa('web' + ":" + 'web')
            },
            url: 'https://' + URL + '/oauth/token.php',
            data: {
                grant_type: 'password',
                username:u,
                password:p,
                scope:'get_data get_tables webprofile'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
                location.reload();
            }, 
            error: function() {
                alert('Log in failed!');
            }
        });
    }

    function getCookie(cookieName) {
      let cookie = {};
      document.cookie.split(';').forEach(function(el) {
        let [key,value] = el.split('=');
        cookie[key.trim()] = value;
      })
      return cookie[cookieName];
    }
    function setCookie(cname, cvalue, exhours) {
        const d = new Date();
        d.setTime(d.getTime() + ((exhours+3)*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        let cookie = {
            "expiry":d.setTime(d.getTime() + (exhours*60*60*1000)),
            "data":{}
        }
        cookie["data"][cname] = cvalue;
        document.cookie = cname + "=" + JSON.stringify(cookie) + ";" + expires + ";path=/";
    }
    function Close(e) {
        let el = document.getElementById(e);
        el.style.display = "none";
    }
