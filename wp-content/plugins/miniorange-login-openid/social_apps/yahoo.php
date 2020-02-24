<?php


class yahoo
{
    public $color="#400092";
    public $scope="";
    public $video_url="https://www.youtube.com/embed/LVPqnIicAZQ";
    public $instructions;
    public function __construct()
    {
        $this->site_url = get_option('siteurl');
        $this->instructions = "Go to <a href=\"https://developer.yahoo.com\" target=\"_blank\">https://developer.yahoo.com</a> Sign in with your yahoo account and select Apps from the menubar.##On the page, Click on the <strong>Create an App</strong> button.##Enter <strong>Application Name</strong> and select <strong>Application Type</strong> as <strong>Web Application</strong>.##Enter <b><code id='13'>".get_option('siteurl')."</code><i style= \"width: 11px;height: 9px;padding-left:2px;padding-top:3px\" class=\"mofa mofa-fw mofa-lg mofa-copy mo_copy mo_copytooltip\" onclick=\"copyToClipboard(this, '#13', '#shortcode_url_copy')\"><span id=\"shortcode_url_copy\" class=\"mo_copytooltiptext\">Copy to Clipboard</span></i></b> in the <strong>Home Page URL</strong>.##Enter <b><code id='14'>".mo_get_permalink('yahoo')."</code><i style= \"width: 11px;height: 9px;padding-left:2px;padding-top:3px\" class=\"mofa mofa-fw mofa-lg mofa-copy mo_copy mo_copytooltip\" onclick=\"copyToClipboard(this, '#14', '#shortcode_url1_copy')\"><span id=\"shortcode_url1_copy\" class=\"mo_copytooltiptext\">Copy to Clipboard</span></i></b></b> in <b>Callback Domain</b>##Select all <b>API Permissions</b>.##Click on <strong>Create App</strong>.##Copy the <b>Client ID</b> and <b>Client Secret</b> from this page and Paste them into the fields above.##Keep <b>Scope</b> blank. ##Click on the <b>Save settings</b> button.</li><li>Go to Social Login tab to configure the display as well as other login settings";
    }

    function mo_openid_get_app_code()
    {
        $appslist = maybe_unserialize(get_option('mo_openid_apps_list'));
        $social_app_redirect_uri= get_social_app_redirect_uri('yahoo');
        mo_openid_start_session();
        $_SESSION["appname"] = 'yahoo';
        $client_id = $appslist['yahoo']['clientid'];
        $login_dialog_url= 'https://api.login.yahoo.com/oauth2/request_auth?client_id=' . $client_id . '&response_type=code&redirect_uri='.$social_app_redirect_uri.'&nonce=YihsFwGKgt3KJUhkd5m1';
        header('Location:'. $login_dialog_url);
        exit;
    }

    function mo_openid_get_access_token()
    {
        $code = mo_openid_validate_code();
        $social_app_redirect_uri = get_social_app_redirect_uri('yahoo');

        $appslist = maybe_unserialize(get_option('mo_openid_apps_list'));
        $client_id = $appslist['yahoo']['clientid'];
        $client_secret = $appslist['yahoo']['clientsecret'];
        $access_token_uri = 'https://api.login.yahoo.com/oauth2/get_token';
        $postData = 'grant_type=authorization_code&client_id='. $client_id .'&client_secret=' . $client_secret . '&code=' . $code . '&redirect_uri=' . $social_app_redirect_uri;

        $access_token_json_output = mo_openid_get_access_token($postData, $access_token_uri,'yahoo');

        $access_token = isset($access_token_json_output['access_token']) ? $access_token_json_output['access_token'] : '';
        mo_openid_start_session();

        $guid = $access_token_json_output['xoauth_yahoo_guid'];
        $profile_url = 'https://social.yahooapis.com/v1/user/'.$guid.'/profile?format=json';

        $profile_json_output = mo_openid_get_social_app_data($access_token, $profile_url,'yahoo');

        //Test Configuration
        if (is_user_logged_in() && get_option('mo_openid_test_configuration') == 1) {
            mo_openid_app_test_config($profile_json_output);
        }
        //set all profile details
        //Set User current app
        $first_name = $last_name = $email = $user_name = $user_url = $user_picture = $social_user_id = '';
        $location_city = $location_country = $about_me = $company_name = $age = $gender = $friend_nos = '';

        if(isset($profile_json_output['profile']['nickname'])){
            $user_name = isset( $profile_json_output['profile']['nickname']) ? $profile_json_output['profile']['nickname'] : '';
        }
        if(isset($profile_json_output['profile']['image']['imageUrl'])){
            $user_picture = isset( $profile_json_output['profile']['image']['imageUrl']) ? $profile_json_output['profile']['image']['imageUrl'] : '';
        }
        if(isset($profile_json_output['profile']['profileUrl'])){
            $user_url = isset( $profile_json_output['profile']['profileUrl']) ? $profile_json_output['profile']['profileUrl'] : '';
        }
        if(isset($profile_json_output['profile']['givenName'])){
            $first_name = isset( $profile_json_output['profile']['givenName']) ? $profile_json_output['profile']['givenName'] : '';
        }
        if(isset($profile_json_output['profile']['familyName'])){
            $last_name = isset( $profile_json_output['profile']['familyName']) ? $profile_json_output['profile']['familyName'] : '';
        }
        if(isset($profile_json_output['profile']['emails'][0]['handle'])){
            $email = isset( $profile_json_output['profile']['emails'][0]['handle']) ? $profile_json_output['profile']['emails'][0]['handle'] : '';
        }
        if(isset($profile_json_output['profile']['guid'])){
            $social_user_id=isset( $profile_json_output['profile']['guid']) ? $profile_json_output['profile']['guid'] : '';
        }

        $appuserdetails = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'user_name' => $user_name,
            'user_url' => $user_url,
            'user_picture' => $user_picture,
            'social_user_id' => $social_user_id,
            'location_city' => $location_city,
            'location_country' => $location_country,
            'about_me' => $about_me,
            'company_name' => $company_name,
            'friend_nos' => $friend_nos,
            'gender' => $gender,
            'age' => $age,
        );
        return $appuserdetails;
    }
}