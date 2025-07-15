<?php
//For feedback, suggestions, or issues please visit https://www.mattsshack.com/plex-movie-poster-display/
include_once('../assets/plexmovieposter/loginCheck.php');
include '../config.php';
include '../assets/plexmovieposter/CommonLib.php';
// include '../assets/plexmovieposter/tools.php';
include '../assets/plexmovieposter/CacheLib.php';
include '../assets/plexmovieposter/setData.php';

//Save Configuration
if (!empty($_POST['saveConfig'])) {
    setData(basename(__FILE__));
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php HeaderInfo(basename(__FILE__)); ?>
    <script> ShowHideAdvanced(); </script>
    <script> ShowHideSideBar(); </script>
    <style>
        #testResult {
            margin-top: 15px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        #testResult.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        #testResult.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        #testResult .server-info {
            margin-top: 10px;
        }
        #testResult .library-list {
            margin-top: 10px;
            padding-left: 20px;
        }
        .test-btn {
            margin-left: 10px;
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div id="plex" class="application">
        <div class="background-container">
            <div class="settings-core"></div>
        </div>
        <?php NavBar() ;?>
        <div id="content" class="scroll-container dark-scrollbar">
            <div class="FullPage-container-17Y0cs">
                <?php sidebarInfo(basename(__FILE__)) ;?>
                <div class="Page-page-aq7i_X Scroller-scroller-3GqQcZ Scroller-vertical-VScFLT  ">
                    <div id="MainPage" class="SettingsPage-content-1vKVEr PageContent-pageContent-16mK6G">
                        <h2 class="SettingsPageHeader-header-1ugtIL">
                            PLEX Configuration
                        </h2>
                        <?php AdvancedBar() ;?>
                        <form id="server-settings-form" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                            <!-- SEGMENT BLOCK START -->
                                <div class="form-group">
                                    <h3>Server Configuration</h3>
                                </div>

                                <div class="form-group">
                                    Plex Server (IP or Hostname):&nbsp;

                                    <input type="text" class="fieldInfo-medium form-control form-inline" id="plexServer" name="plexServer" maxlength="255"
                                        placeholder="Plex Server IP or Hostname" value="<?php echo $plexServer; ?>" required>

                                    <!-- <p class="help-block">
                                        A Plex server IP address is required.
                                    </p> -->
                                </div>

                                <div class="form-group" id="token_view">
                                    Plex Token:&nbsp;

                                    <a href="https://support.plex.tv/hc/en-us/articles/204059436-Finding-your-account-token-X-Plex-Token" target=_blank>
                                        <span class="badge badge-primary">?</span>
                                    </a>
                                    &nbsp;

                                    <input type="password" class="fieldInfo-token form-control form-inline" id="plexToken" name="plexToken"
                                        placeholder="Plex Token" value="<?php echo $plexToken; ?>" required>
                                        &nbsp;
                                    <button class="btn " type="button" id="token_view_btn" onclick="tokenView()">Show</button>

                                    <!-- <p class="help-block">
                                        A Plex token is required.
                                    </p> -->
                                </div>

                                <div class="form-group">
                                    Plex Movie Sections:&nbsp;

                                    <input type="text" class="fieldInfo-medium form-control form-inline" id="plexServerMovieSection"
                                        name="plexServerMovieSection" placeholder="Plex Movie Sections"
                                        value="<?php echo $plexServerMovieSection; ?>" required>

                                    <p class="help-block">
                                        <small>Comma Separated with no Spaces.  At least one Plex movie sections is required.</small>
                                    </p>
                                </div>

                                <div class="form-group advanced-setting">
                                    Enable Plex SSL connection:&nbsp;

                                    <label class="switch">
                                    <input type="checkbox" name="plexServerSSL" id="plexServerSSL" value="1" <?php if ($plexServerSSL) echo " checked"?>>
                                    <span class="slider round"></span>
                                    </label>

                                    <!-- <p class="help-block">
                                    </p> -->
                                </div>

                                <div class="form-group advanced-setting">
                                    Plex Server Direct address:
                                    <a href="https://support.plex.tv/articles/206225077-how-to-use-secure-server-connections/" target=_blank>
                                        <span class="badge badge-primary">?</span>
                                    </a>
                                    &nbsp;

                                    <input type="text" class="fieldInfo-3xlarge form-control" id="plexServerDirect" name="plexServerDirect" maxlength="65"
                                        placeholder="Plex Server Direct" value="<?php echo $plexServerDirect; ?>" required>

                                    <p class="help-block">
                                        <small>A Plex server direct URL is required (.plex.direct).</small>
                                    </p>
                                </div>

                                <!-- Test Connection Button -->
                                <div class="form-group">
                                    <button type="button" class="btn btn-info" id="testConnectionBtn" onclick="testPlexConnection()">
                                        Test Connection
                                    </button>
                                    <div id="testResult"></div>
                                </div>

                                <div class="form-group">
                                    <hr>
                                    <h3>Client Configuration</h3>
                                </div>

                                <div class="form-group">
                                    <!-- Max Length is 3 IP address 15chr per (IP Length) + 2 for comma -->
                                    Plex Client IP:&nbsp;
                                    <input type="text" class="fieldInfo-ipaddress form-control form-inline" id="plexClient" name="plexClient" maxlength="47"
                                        placeholder="Plex Client IP" value="<?php echo $plexClient; ?>" required>

                                    <p class="help-block">
                                        A Plex client IP address is required. For multiple clients, use comma separation with no spaces.
                                    </p>
                                </div>

                                <div class="form-group">
                                    Plex Client Name:&nbsp;

                                    <input type="text" class="fieldInfo-xlarge form-control form-inline" id="plexClientName" name="plexClientName"
                                        placeholder="Plex Client Name" value="<?php echo $plexClientName; ?>">

                                    <p class="help-block">
                                        A Plex client IP name is optional. For multiple clients, use comma separation with no spaces.
                                    </p>
                                </div>
                            <!-- SEGMENT BLOCK END -->

                            <!-- GHOST BLOCK START -->
                                <?php ghostData(basename(__FILE__)) ;?>
                            <!-- GHOST BLOCK END -->

                            <!-- SUBMIT BLOCK START -->
                                <?php submitForm(FALSE); ?>
                            <!-- SUBMIT BLOCK END -->
                        </form>
                        <?php FooterInfo(4) ; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function testPlexConnection() {
        var btn = document.getElementById('testConnectionBtn');
        var resultDiv = document.getElementById('testResult');

        // Get current form values
        var server = document.getElementById('plexServer').value;
        var token = document.getElementById('plexToken').value;
        var ssl = document.getElementById('plexServerSSL').checked ? '1' : '0';
        var serverDirect = document.getElementById('plexServerDirect').value;

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span>Testing...';
        resultDiv.style.display = 'none';

        // Make AJAX request
        $.ajax({
            url: '../assets/plexmovieposter/testPlexConnection.php',
            method: 'POST',
            data: {
                server: server,
                token: token,
                ssl: ssl,
                serverDirect: serverDirect
            },
            dataType: 'json',
            timeout: 15000,
            success: function(response) {
                btn.disabled = false;
                btn.innerHTML = 'Test Connection';
                resultDiv.style.display = 'block';

                if (response.success) {
                    resultDiv.className = 'success';
                    var html = '<strong>Connection Successful!</strong>';
                    html += '<div class="server-info">';
                    html += '<strong>Server:</strong> ' + escapeHtml(response.serverName) + '<br>';
                    html += '<strong>Version:</strong> ' + escapeHtml(response.version) + '<br>';
                    html += '<strong>Platform:</strong> ' + escapeHtml(response.platform) + '<br>';
                    html += '<strong>Connection:</strong> ' + response.scheme + '://' + escapeHtml(response.server) + ':32400';
                    html += '</div>';

                    if (response.libraries && response.libraries.length > 0) {
                        html += '<div class="library-list"><strong>Libraries Found:</strong><ul>';
                        response.libraries.forEach(function(lib) {
                            html += '<li>[' + escapeHtml(lib.id) + '] ' + escapeHtml(lib.title) + ' (' + escapeHtml(lib.type) + ')</li>';
                        });
                        html += '</ul></div>';
                    }

                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = '<strong>Connection Failed</strong><br>' + escapeHtml(response.error);
                }
            },
            error: function(xhr, status, error) {
                btn.disabled = false;
                btn.innerHTML = 'Test Connection';
                resultDiv.style.display = 'block';
                resultDiv.className = 'error';

                if (status === 'timeout') {
                    resultDiv.innerHTML = '<strong>Connection Failed</strong><br>Request timed out - server may be unreachable';
                } else {
                    resultDiv.innerHTML = '<strong>Connection Failed</strong><br>Error: ' + escapeHtml(error || 'Unknown error');
                }
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>

    <?php safariJSSide(); ?>
</body>
</html>
