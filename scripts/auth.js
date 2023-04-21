    // Get the modal
    var modal = document.getElementById("myAuthModal");

    let at_expiry = false;
    let rt_expiry = false;
    const d = new Date();
    if (jat !== 'undefined') {
        const p1 = JSON.parse(jat);
        const p2 = JSON.parse(jrt);
        at_expiry = eval(p1.expiry - (d.getTime()/1000));
        rt_expiry = eval(p2.expiry - (d.getTime()/1000));
    }

    let access_token;
    let refresh_token;
    if (jrt === 'undefined') {
        // No refresh token

        //modal.style.display = "block";

    } else if (jrt !== 'undefined' && !at_expiry && rt_expiry) {
        // Has refresh token, and not expired but access_token expired
        const jrt_p = JSON.parse(jrt);
        refresh_token = jrt_p.data.refresh_token;

        $.ajax({
            type: "POST",
            url: 'https://' + URL + '/oauth/token.php',
            headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
            data: {
                refresh_token: refresh_token,
                client_id: 'web',
                client_secret: 'web',
                grant_type:'refresh_token'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
            }, 
            error: function() {
                alert('Log in first!');
            }
        });
    } else if (jrt !== 'undefined'){
        // Has an access token which not expired
        console.log('Refreshing tokens with valid access token');
        const jrt_p = JSON.parse(jrt);
        refresh_token = jrt_p.data.refresh_token;
        $.ajax({
            type: "POST",
            url: 'https://' + URL + '/oauth/token.php',
            headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
            data: {
                refresh_token: refresh_token,
                client_id: 'web',
                client_secret: 'web',
                grant_type:'refresh_token'
            },
            async: false,
            dataType: 'json',
            success: function (response) {
                access_token = response['access_token'];
                refresh_token = response['refresh_token'];
                setCookie('access_token',access_token,1);
                setCookie('refresh_token',refresh_token,336);
            }, 
            error: function() {
                console.log('Invalid refresh tokens....');
                // Invalid refresh token has been used. It has been kicked out by a concurrent request
                //alert('Token refreshing failed, log in to access resources!');
                if (jat !== 'undefined') {
                    const jat_p = JSON.parse(jat);
                    access_token = jat_p.data.access_token;
                    $.ajax({
                        type: "POST",
                        url: 'https://' + URL + '/oauth/token.php',
                        headers: {'Content-type': 'application/x-www-form-urlencoded','Authorization':"Basic " + btoa('web' + ":" + 'web')},
                        data: {
                            access_token: access_token,
                            client_id: 'web',
                            client_secret: 'web',
                            grant_type:'refresh_token'
                        },
                        async: false,
                        dataType: 'json',
                        success: function (response) {
                            access_token = response['access_token'];
                            refresh_token = response['refresh_token'];
                            setCookie('access_token',access_token,1);
                            setCookie('refresh_token',refresh_token,336);
                        }, 
                        error: function() {
                            console.log('Invalid access tokens....');
                            modal.style.display = "block";
                            // Invalid refresh token has been used. It has been kicked out by a concurrent request
                            //alert('Token refreshing failed, log in to access resources!');
                        }
                    })
                }
            }
        });
    }

    let tables;
    $.ajax({
        type: "POST",
        url: 'https://' + URL + '/v2.4/pds.php',
        data: {
            access_token: access_token,
            scope: 'get_tables',
            table: target_table 
        },
        dataType: 'json',
        success: function (response) {
            tables = response['data'];
            tables.sort();
            let x = tables.map(function(v) {
                return $('<option/>', {
                  value: v,
                  text: v
                })
            });
            $('#table_list').append(x);
            $('#table_list').val(target_table);
                
         },
        error: function () {
            //alert("MAP connection error!");
        }
    });

