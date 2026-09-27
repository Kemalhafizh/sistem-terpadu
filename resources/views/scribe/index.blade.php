<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>Sistem Terpadu Sultan Agung 1 API</title>

  <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
  <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

  {{-- Served from our build, not public CDNs (see App\Support\VendorAsset). Generated from
       resources/views/vendor/scribe/themes/default/index.blade.php, so scribe:generate keeps this. --}}
  <x-vendor.script lib="lodash" />

  <x-vendor.style lib="highlightjs-obsidian" />
  <x-vendor.script lib="highlightjs" />

  <x-vendor.script lib="jets" />

  <style id="language-style">
    /* starts out as display none and is replaced with js later */
          body .content .bash-example code { display: none; }
          body .content .javascript-example code { display: none; }
      </style>

  <script>
    var tryItOutBaseUrl = "https://sistem-terpadu.test";
    var useCsrf = Boolean();
    var csrfUrl = "/sanctum/csrf-cookie";
  </script>
  <script src="{{ asset("/vendor/scribe/js/tryitout-5.10.0.js") }}"></script>

  <script src="{{ asset("/vendor/scribe/js/theme-default-5.10.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
  <span>
    MENU
    <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
  </span>
</a>
<div class="tocify-wrapper">
  
      <div class="lang-selector">
                      <button type="button" class="lang-button" data-language-name="bash">bash</button>
                      <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
          </div>
  
  <div class="search">
    <input type="text" class="search" id="input-search" placeholder="Search">
  </div>

  <div id="toc">
          <ul id="tocify-header-introduction" class="tocify-header">
        <li class="tocify-item level-1" data-unique="introduction">
          <a href="#introduction">Introduction</a>
        </li>
              </ul>
          <ul id="tocify-header-authenticating-requests" class="tocify-header">
        <li class="tocify-item level-1" data-unique="authenticating-requests">
          <a href="#authenticating-requests">Authenticating requests</a>
        </li>
              </ul>
          <ul id="tocify-header-authentication" class="tocify-header">
        <li class="tocify-item level-1" data-unique="authentication">
          <a href="#authentication">Authentication</a>
        </li>
                  <ul id="tocify-subheader-authentication" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="authentication-GETapi-v1-profile">
                <a href="#authentication-GETapi-v1-profile">Get Profile</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-authentication-v1" class="tocify-header">
        <li class="tocify-item level-1" data-unique="authentication-v1">
          <a href="#authentication-v1">Authentication v1</a>
        </li>
                  <ul id="tocify-subheader-authentication-v1" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="authentication-v1-POSTapi-v1-login">
                <a href="#authentication-v1-POSTapi-v1-login">User Login</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="authentication-v1-POSTapi-v1-logout">
                <a href="#authentication-v1-POSTapi-v1-logout">User Logout</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="authentication-v1-POSTapi-v1-refresh-token">
                <a href="#authentication-v1-POSTapi-v1-refresh-token">Refresh Token</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="authentication-v1-GETapi-v1-me">
                <a href="#authentication-v1-GETapi-v1-me">Get Current Profile (Me)</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-endpoints" class="tocify-header">
        <li class="tocify-item level-1" data-unique="endpoints">
          <a href="#endpoints">Endpoints</a>
        </li>
                  <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="endpoints-GETapi-health">
                <a href="#endpoints-GETapi-health">Application health check endpoint.</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="endpoints-POSTapi-midtrans-webhook">
                <a href="#endpoints-POSTapi-midtrans-webhook">Handle webhook callback dari Midtrans (Sandbox & Simulator).</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="endpoints-GETapi-academic-events">
                <a href="#endpoints-GETapi-academic-events">GET api/academic-events</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-mobile-application-data" class="tocify-header">
        <li class="tocify-item level-1" data-unique="mobile-application-data">
          <a href="#mobile-application-data">Mobile Application Data</a>
        </li>
                  <ul id="tocify-subheader-mobile-application-data" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students">
                <a href="#mobile-application-data-GETapi-v1-students">Get Students List</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--attendances">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--attendances">Get Attendances</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--grades">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--grades">Get Grades</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--billings">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--billings">Get Billings</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--point-logs">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--point-logs">GET api/v1/students/{uuid}/point-logs</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--tahfidz">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--tahfidz">GET api/v1/students/{uuid}/tahfidz</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="mobile-application-data-GETapi-v1-students--uuid--mutabaah">
                <a href="#mobile-application-data-GETapi-v1-students--uuid--mutabaah">GET api/v1/students/{uuid}/mutabaah</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-parent-scoped-data-v1" class="tocify-header">
        <li class="tocify-item level-1" data-unique="parent-scoped-data-v1">
          <a href="#parent-scoped-data-v1">Parent Scoped Data v1</a>
        </li>
                  <ul id="tocify-subheader-parent-scoped-data-v1" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-children">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-children">Get Children List</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-child--student--summary">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-child--student--summary">Get Child Summary</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-child--student--grades">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-child--student--grades">Get Child Grades</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-child--student--attendance">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-child--student--attendance">Get Child Attendance</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-child--student--billings">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-child--student--billings">Get Child Billings</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-child--student--points">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-child--student--points">Get Child BK Points</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-GETapi-v1-parent-messages">
                <a href="#parent-scoped-data-v1-GETapi-v1-parent-messages">Get Messages Chat History</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="parent-scoped-data-v1-POSTapi-v1-parent-messages">
                <a href="#parent-scoped-data-v1-POSTapi-v1-parent-messages">Send Message to Teacher</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-student-scoped-data-v1" class="tocify-header">
        <li class="tocify-item level-1" data-unique="student-scoped-data-v1">
          <a href="#student-scoped-data-v1">Student Scoped Data v1</a>
        </li>
                  <ul id="tocify-subheader-student-scoped-data-v1" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-profile">
                <a href="#student-scoped-data-v1-GETapi-v1-student-profile">Get Student Profile</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-schedule">
                <a href="#student-scoped-data-v1-GETapi-v1-student-schedule">Get Student Schedule</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-grades">
                <a href="#student-scoped-data-v1-GETapi-v1-student-grades">Get Student Grades</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-attendance">
                <a href="#student-scoped-data-v1-GETapi-v1-student-attendance">Get Student Attendance</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-billings">
                <a href="#student-scoped-data-v1-GETapi-v1-student-billings">Get Student Billings</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-points">
                <a href="#student-scoped-data-v1-GETapi-v1-student-points">Get Student BK Points</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-GETapi-v1-student-notifications">
                <a href="#student-scoped-data-v1-GETapi-v1-student-notifications">Get Student Notifications</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="student-scoped-data-v1-POSTapi-v1-student-notifications--id--read">
                <a href="#student-scoped-data-v1-POSTapi-v1-student-notifications--id--read">Mark Notification as Read</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-teacher-scoped-data-v1" class="tocify-header">
        <li class="tocify-item level-1" data-unique="teacher-scoped-data-v1">
          <a href="#teacher-scoped-data-v1">Teacher Scoped Data v1</a>
        </li>
                  <ul id="tocify-subheader-teacher-scoped-data-v1" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="teacher-scoped-data-v1-GETapi-v1-teacher-schedule">
                <a href="#teacher-scoped-data-v1-GETapi-v1-teacher-schedule">Get Teacher Schedule</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="teacher-scoped-data-v1-GETapi-v1-teacher-classes">
                <a href="#teacher-scoped-data-v1-GETapi-v1-teacher-classes">Get Taught Classrooms</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="teacher-scoped-data-v1-POSTapi-v1-teacher-attendance">
                <a href="#teacher-scoped-data-v1-POSTapi-v1-teacher-attendance">Store Bulk Attendance</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="teacher-scoped-data-v1-GETapi-v1-teacher-journals">
                <a href="#teacher-scoped-data-v1-GETapi-v1-teacher-journals">Get Teaching Journals</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="teacher-scoped-data-v1-POSTapi-v1-teacher-journals">
                <a href="#teacher-scoped-data-v1-POSTapi-v1-teacher-journals">Store Teaching Journal</a>
              </li>
                                    </ul>
              </ul>
          <ul id="tocify-header-universal-mobile-data-v1" class="tocify-header">
        <li class="tocify-item level-1" data-unique="universal-mobile-data-v1">
          <a href="#universal-mobile-data-v1">Universal Mobile Data v1</a>
        </li>
                  <ul id="tocify-subheader-universal-mobile-data-v1" class="tocify-subheader">
                          <li class="tocify-item level-2" data-unique="universal-mobile-data-v1-GETapi-v1-announcements">
                <a href="#universal-mobile-data-v1-GETapi-v1-announcements">Get Announcements</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="universal-mobile-data-v1-GETapi-v1-calendar">
                <a href="#universal-mobile-data-v1-GETapi-v1-calendar">Get Calendar Events</a>
              </li>
                                        <li class="tocify-item level-2" data-unique="universal-mobile-data-v1-GETapi-v1-notifications">
                <a href="#universal-mobile-data-v1-GETapi-v1-notifications">Get Notifications</a>
              </li>
                                    </ul>
              </ul>
      </div>

  <ul class="toc-footer" id="toc-footer">
          <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
              <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
        <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe </a></li>
  </ul>

  <ul class="toc-footer" id="last-updated">
    <li>Last updated: July 17, 2026</li>
  </ul>
</div>

<div class="page-wrapper">
  <div class="dark-box"></div>
  <div class="content">
    <h1 id="introduction">Introduction</h1>
<p>Dokumentasi API untuk aplikasi mobile (Siswa &amp; Orang Tua) Sistem Terpadu Sultan Agung 1.</p>
<aside>
  <strong>Base URL</strong>: <code>https://sistem-terpadu.test</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

    <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.</p>

    <h1 id="authentication">Authentication</h1>

  <p>API endpoint untuk manajemen otentikasi pengguna aplikasi mobile (Siswa/Orang Tua).</p>

                <h2 id="authentication-GETapi-v1-profile">Get Profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mendapatkan profil pengguna yang sedang login beserta data siswa (jika role = student).</p>

<span id="example-requests-GETapi-v1-profile">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/profile" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/profile"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-profile">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-profile" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-profile"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-profile"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-profile" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-profile" data-method="GET"
   data-path="api/v1/profile"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-profile', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-profile"
          onclick="tryItOut('GETapi-v1-profile');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-profile"
          onclick="cancelTryOut('GETapi-v1-profile');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-profile"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/profile</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-profile"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-profile"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-profile"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

        <h1 id="authentication-v1">Authentication v1</h1>

  <p>API endpoint untuk mengelola autentikasi berbasis Sanctum token untuk aplikasi mobile.</p>

                <h2 id="authentication-v1-POSTapi-v1-login">User Login</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Autentikasi kredensial pengguna dan dapatkan token.
Menerapkan rotasi token berbasis nama perangkat (device_name).</p>

<span id="example-requests-POSTapi-v1-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/login" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json" \
  --data "{
  \"email\": \"siswa@sekolah.com\",
  \"password\": \"password123\",
  \"device_name\": \"Android Phone\"
}"
</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/login"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};

let body = {
  "email": "siswa@sekolah.com",
  "password": "password123",
  "device_name": "Android Phone"
};

fetch(url, {
  method: "POST",
  headers,
  body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-login">
</span>
<span id="execution-results-POSTapi-v1-login" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-login"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-login"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-login" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-login" data-method="POST"
   data-path="api/v1/login"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-login', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-login"
          onclick="tryItOut('POSTapi-v1-login');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-login"
          onclick="cancelTryOut('POSTapi-v1-login');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-login"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/login</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-login"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-login"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-login"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
    <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="email"        data-endpoint="POSTapi-v1-login"
        value="siswa@sekolah.com"
        data-component="body">
  <br>
<p>Email pengguna. Example: <code>siswa@sekolah.com</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="password"        data-endpoint="POSTapi-v1-login"
        value="password123"
        data-component="body">
  <br>
<p>Kata sandi pengguna. Example: <code>password123</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="device_name"        data-endpoint="POSTapi-v1-login"
        value="Android Phone"
        data-component="body">
  <br>
<p>Nama perangkat penelusuran. Example: <code>Android Phone</code></p>
    </div>
    </form>

          <h2 id="authentication-v1-POSTapi-v1-logout">User Logout</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mencabut token aktif saat ini.</p>

<span id="example-requests-POSTapi-v1-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/logout" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/logout"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "POST",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-logout">
</span>
<span id="execution-results-POSTapi-v1-logout" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-logout"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-logout"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-logout" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-logout" data-method="POST"
   data-path="api/v1/logout"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-logout', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-logout"
          onclick="tryItOut('POSTapi-v1-logout');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-logout"
          onclick="cancelTryOut('POSTapi-v1-logout');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-logout"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/logout</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-logout"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-logout"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-logout"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="authentication-v1-POSTapi-v1-refresh-token">Refresh Token</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Menghapus token akses aktif saat ini dan menghasilkan token baru dengan nama perangkat yang sama.</p>

<span id="example-requests-POSTapi-v1-refresh-token">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/refresh-token" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/refresh-token"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "POST",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-refresh-token">
</span>
<span id="execution-results-POSTapi-v1-refresh-token" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-refresh-token"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-refresh-token"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-refresh-token" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-refresh-token">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-refresh-token" data-method="POST"
   data-path="api/v1/refresh-token"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-refresh-token', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-refresh-token"
          onclick="tryItOut('POSTapi-v1-refresh-token');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-refresh-token"
          onclick="cancelTryOut('POSTapi-v1-refresh-token');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-refresh-token"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/refresh-token</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-refresh-token"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-refresh-token"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-refresh-token"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="authentication-v1-GETapi-v1-me">Get Current Profile (Me)</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil ringkasan profil lengkap pengguna saat ini.</p>

<span id="example-requests-GETapi-v1-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/me" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/me"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-me" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-me"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-me"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me" data-method="GET"
   data-path="api/v1/me"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-me"
          onclick="tryItOut('GETapi-v1-me');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-me"
          onclick="cancelTryOut('GETapi-v1-me');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-me"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/me</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-me"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-me"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-me"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

        <h1 id="endpoints">Endpoints</h1>

  

                <h2 id="endpoints-GETapi-health">Application health check endpoint.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the status of core services: database, cache, and storage.
Used by load balancers, monitoring tools, and Docker healthchecks.</p>

<span id="example-requests-GETapi-health">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/health" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/health"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-health">
      <blockquote>
      <p>Example response (200):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;status&quot;: &quot;healthy&quot;,
  &quot;timestamp&quot;: &quot;2026-07-17T17:02:52+07:00&quot;,
  &quot;version&quot;: &quot;1.0.0&quot;,
  &quot;checks&quot;: {
    &quot;database&quot;: {
      &quot;status&quot;: &quot;ok&quot;,
      &quot;latency_ms&quot;: 0.25
    },
    &quot;cache&quot;: {
      &quot;status&quot;: &quot;ok&quot;
    },
    &quot;storage&quot;: {
      &quot;status&quot;: &quot;ok&quot;
    }
  }
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-health" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-health"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-health"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-health" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-health">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-health" data-method="GET"
   data-path="api/health"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-health', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-health"
          onclick="tryItOut('GETapi-health');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-health"
          onclick="cancelTryOut('GETapi-health');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-health"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/health</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-health"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-health"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-health"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="endpoints-POSTapi-midtrans-webhook">Handle webhook callback dari Midtrans (Sandbox &amp; Simulator).</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-midtrans-webhook">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/midtrans/webhook" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/midtrans/webhook"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "POST",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-midtrans-webhook">
</span>
<span id="execution-results-POSTapi-midtrans-webhook" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-midtrans-webhook"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-midtrans-webhook"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-midtrans-webhook" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-midtrans-webhook">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-midtrans-webhook" data-method="POST"
   data-path="api/midtrans/webhook"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-midtrans-webhook', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-midtrans-webhook"
          onclick="tryItOut('POSTapi-midtrans-webhook');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-midtrans-webhook"
          onclick="cancelTryOut('POSTapi-midtrans-webhook');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-midtrans-webhook"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/midtrans/webhook</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-midtrans-webhook"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-midtrans-webhook"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-midtrans-webhook"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="endpoints-GETapi-academic-events">GET api/academic-events</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-academic-events">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/academic-events" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json" \
  --data "{
  \"start\": \"2026-07-17T17:02:53\",
  \"end\": \"2026-07-17T17:02:53\",
  \"type\": \"architecto\",
  \"classroom_id\": 16
}"
</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/academic-events"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};

let body = {
  "start": "2026-07-17T17:02:53",
  "end": "2026-07-17T17:02:53",
  "type": "architecto",
  "classroom_id": 16
};

fetch(url, {
  method: "GET",
  headers,
  body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-academic-events">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
set-cookie: XSRF-TOKEN=eyJpdiI6IjVoVkhRdHQ1TittRTlVM3U4TFNwSHc9PSIsInZhbHVlIjoiWWJnbWVXSEdneG1BZmZJRjhxdVlrU3RqTlc3bC9LM09RVXBLK09lTjRSdWpQblN3eGJyMkwzSlFGOE9PSndMY0ZKSVQ0R29aSEtiNlA3czU1R1Mwbi9LUm9yS0pBYTF0QnRGK2hlZVpQVEdkY3lrbTJ5MkVqRitBTHM2anFZSFYiLCJtYWMiOiI2ODVhMmUwNzZmYzgyMTFiNmQ1M2JmNzg0OTk0ZmFkYzRmZWRiOTExN2Y3OGZkMmY3ZDk1ZjJkMmU5MWRkNDliIiwidGFnIjoiIn0%3D; expires=Fri, 17 Jul 2026 12:02:53 GMT; Max-Age=7200; path=/; samesite=lax; sista-sma-isa-1-session=eyJpdiI6IjIwZFZoWUlydFVYaExRcHE3QzdGK3c9PSIsInZhbHVlIjoiZVd2c0daMDNKZWRBY3Y2dVAxUUFINjJrS3BNK3J0WEhWMEFpRENHRmUyMDlyY3puTUljUG1XWnR3N2VwZkhMT0pPaGIwUFZyckJPM1B4OXdwK0hjUDBGMUFlRmtuc1phZ3lIeTc4eHIxdXptcjBlTU4yU2VpbHdNYXhZUlYyUG0iLCJtYWMiOiI1Mzk5N2NhMGUyNDJlYTJmNzllYjZkMmI4Yzk5OWJhMTAyZDYzMzMyMjJjMTA5ZGIzNGY5ZjI2YTNmYjUwYjZhIiwidGFnIjoiIn0%3D; expires=Fri, 17 Jul 2026 12:02:53 GMT; Max-Age=7200; path=/; httponly; samesite=lax
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-academic-events" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-academic-events"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-academic-events"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-academic-events" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-academic-events">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-academic-events" data-method="GET"
   data-path="api/academic-events"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-academic-events', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-academic-events"
          onclick="tryItOut('GETapi-academic-events');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-academic-events"
          onclick="cancelTryOut('GETapi-academic-events');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-academic-events"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/academic-events</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-academic-events"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-academic-events"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-academic-events"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
    <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>start</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="start"        data-endpoint="GETapi-academic-events"
        value="2026-07-17T17:02:53"
        data-component="body">
  <br>
<p>validation.date. Example: <code>2026-07-17T17:02:53</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>end</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="end"        data-endpoint="GETapi-academic-events"
        value="2026-07-17T17:02:53"
        data-component="body">
  <br>
<p>validation.date. Example: <code>2026-07-17T17:02:53</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="type"        data-endpoint="GETapi-academic-events"
        value="architecto"
        data-component="body">
  <br>
<p>Example: <code>architecto</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>classroom_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="classroom_id"        data-endpoint="GETapi-academic-events"
        value="16"
        data-component="body">
  <br>
<p>Example: <code>16</code></p>
    </div>
    </form>

        <h1 id="mobile-application-data">Mobile Application Data</h1>

  <p>API endpoint untuk mengambil data akademik, keuangan, dan islami
untuk ditampilkan di aplikasi mobile siswa atau orang tua.</p>

                <h2 id="mobile-application-data-GETapi-v1-students">Get Students List</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar siswa yang berelasi dengan akun yang sedang login.
Jika login sebagai <code>parent</code>, akan mengembalikan daftar anak-anaknya.
Jika login sebagai <code>student</code>, mengembalikan data dirinya sendiri.</p>

<span id="example-requests-GETapi-v1-students">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students" data-method="GET"
   data-path="api/v1/students"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students"
          onclick="tryItOut('GETapi-v1-students');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students"
          onclick="cancelTryOut('GETapi-v1-students');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--attendances">Get Attendances</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mendapatkan riwayat 30 absensi harian terakhir dari siswa tertentu.</p>

<span id="example-requests-GETapi-v1-students--uuid--attendances">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/123e4567-e89b-12d3-a456-426614174000/attendances" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/123e4567-e89b-12d3-a456-426614174000/attendances"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--attendances">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--attendances" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--attendances"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--attendances"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--attendances" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--attendances">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--attendances" data-method="GET"
   data-path="api/v1/students/{uuid}/attendances"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--attendances', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--attendances"
          onclick="tryItOut('GETapi-v1-students--uuid--attendances');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--attendances"
          onclick="cancelTryOut('GETapi-v1-students--uuid--attendances');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--attendances"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/attendances</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--attendances"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--attendances"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--attendances"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--attendances"
        value="123e4567-e89b-12d3-a456-426614174000"
        data-component="url">
  <br>
<p>UUID dari siswa. Example: <code>123e4567-e89b-12d3-a456-426614174000</code></p>
      </div>
          </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--grades">Get Grades</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mendapatkan seluruh rekam jejak nilai (Tugas, UH, PTS, PAS) dari siswa tertentu.</p>

<span id="example-requests-GETapi-v1-students--uuid--grades">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/grades" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/grades"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--grades">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--grades" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--grades"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--grades"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--grades" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--grades">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--grades" data-method="GET"
   data-path="api/v1/students/{uuid}/grades"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--grades', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--grades"
          onclick="tryItOut('GETapi-v1-students--uuid--grades');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--grades"
          onclick="cancelTryOut('GETapi-v1-students--uuid--grades');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--grades"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/grades</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--grades"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--grades"
        value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
        data-component="url">
  <br>
<p>UUID dari siswa. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
      </div>
          </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--billings">Get Billings</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mendapatkan daftar tagihan keuangan beserta status pembayarannya.</p>

<span id="example-requests-GETapi-v1-students--uuid--billings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/billings" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/billings"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--billings">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--billings" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--billings"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--billings"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--billings" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--billings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--billings" data-method="GET"
   data-path="api/v1/students/{uuid}/billings"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--billings', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--billings"
          onclick="tryItOut('GETapi-v1-students--uuid--billings');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--billings"
          onclick="cancelTryOut('GETapi-v1-students--uuid--billings');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--billings"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/billings</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--billings"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--billings"
        value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
        data-component="url">
  <br>
<p>UUID dari siswa. Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
      </div>
          </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--point-logs">GET api/v1/students/{uuid}/point-logs</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-students--uuid--point-logs">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/point-logs" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/point-logs"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--point-logs">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--point-logs" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--point-logs"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--point-logs"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--point-logs" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--point-logs">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--point-logs" data-method="GET"
   data-path="api/v1/students/{uuid}/point-logs"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--point-logs', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--point-logs"
          onclick="tryItOut('GETapi-v1-students--uuid--point-logs');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--point-logs"
          onclick="cancelTryOut('GETapi-v1-students--uuid--point-logs');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--point-logs"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/point-logs</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--point-logs"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--point-logs"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--point-logs"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--point-logs"
        value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
        data-component="url">
  <br>
<p>Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
      </div>
          </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--tahfidz">GET api/v1/students/{uuid}/tahfidz</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-students--uuid--tahfidz">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/tahfidz" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/tahfidz"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--tahfidz">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--tahfidz" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--tahfidz"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--tahfidz"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--tahfidz" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--tahfidz">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--tahfidz" data-method="GET"
   data-path="api/v1/students/{uuid}/tahfidz"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--tahfidz', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--tahfidz"
          onclick="tryItOut('GETapi-v1-students--uuid--tahfidz');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--tahfidz"
          onclick="cancelTryOut('GETapi-v1-students--uuid--tahfidz');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--tahfidz"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/tahfidz</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--tahfidz"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--tahfidz"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--tahfidz"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--tahfidz"
        value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
        data-component="url">
  <br>
<p>Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
      </div>
          </form>

          <h2 id="mobile-application-data-GETapi-v1-students--uuid--mutabaah">GET api/v1/students/{uuid}/mutabaah</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-students--uuid--mutabaah">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/mutabaah" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/students/6ff8f7f6-1eb3-3525-be4a-3932c805afed/mutabaah"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-students--uuid--mutabaah">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-students--uuid--mutabaah" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-students--uuid--mutabaah"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-students--uuid--mutabaah"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-students--uuid--mutabaah" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-students--uuid--mutabaah">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-students--uuid--mutabaah" data-method="GET"
   data-path="api/v1/students/{uuid}/mutabaah"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-students--uuid--mutabaah', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-students--uuid--mutabaah"
          onclick="tryItOut('GETapi-v1-students--uuid--mutabaah');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-students--uuid--mutabaah"
          onclick="cancelTryOut('GETapi-v1-students--uuid--mutabaah');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-students--uuid--mutabaah"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/students/{uuid}/mutabaah</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-students--uuid--mutabaah"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-students--uuid--mutabaah"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-students--uuid--mutabaah"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>uuid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="uuid"        data-endpoint="GETapi-v1-students--uuid--mutabaah"
        value="6ff8f7f6-1eb3-3525-be4a-3932c805afed"
        data-component="url">
  <br>
<p>Example: <code>6ff8f7f6-1eb3-3525-be4a-3932c805afed</code></p>
      </div>
          </form>

        <h1 id="parent-scoped-data-v1">Parent Scoped Data v1</h1>

  <p>API endpoint khusus untuk peran Orang Tua guna memantau data anak-anak mereka dan berkomunikasi dengan guru.</p>

                <h2 id="parent-scoped-data-v1-GETapi-v1-parent-children">Get Children List</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar seluruh anak yang terdaftar untuk orang tua yang aktif.</p>

<span id="example-requests-GETapi-v1-parent-children">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/children" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/children"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-children">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-children" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-children"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-children"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-children" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-children">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-children" data-method="GET"
   data-path="api/v1/parent/children"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-children', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-children"
          onclick="tryItOut('GETapi-v1-parent-children');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-children"
          onclick="cancelTryOut('GETapi-v1-parent-children');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-children"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/children</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-children"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-children"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-children"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-child--student--summary">Get Child Summary</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil rangkuman dasbor indikator akademik, absensi, keuangan, dan poin BK untuk anak tertentu.</p>

<span id="example-requests-GETapi-v1-parent-child--student--summary">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/child/123e4567-e89b-12d3-a456-426614174000/summary" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/child/123e4567-e89b-12d3-a456-426614174000/summary"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-child--student--summary">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-child--student--summary" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-child--student--summary"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-child--student--summary"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-child--student--summary" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-child--student--summary">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-child--student--summary" data-method="GET"
   data-path="api/v1/parent/child/{student}/summary"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-child--student--summary', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-child--student--summary"
          onclick="tryItOut('GETapi-v1-parent-child--student--summary');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-child--student--summary"
          onclick="cancelTryOut('GETapi-v1-parent-child--student--summary');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-child--student--summary"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/child/{student}/summary</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-child--student--summary"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-child--student--summary"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-child--student--summary"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>student</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="student"        data-endpoint="GETapi-v1-parent-child--student--summary"
        value="123e4567-e89b-12d3-a456-426614174000"
        data-component="url">
  <br>
<p>UUID siswa (anak). Example: <code>123e4567-e89b-12d3-a456-426614174000</code></p>
      </div>
          </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-child--student--grades">Get Child Grades</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil riwayat nilai akademik anak.</p>

<span id="example-requests-GETapi-v1-parent-child--student--grades">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/child/architecto/grades" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/child/architecto/grades"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-child--student--grades">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-child--student--grades" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-child--student--grades"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-child--student--grades"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-child--student--grades" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-child--student--grades">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-child--student--grades" data-method="GET"
   data-path="api/v1/parent/child/{student}/grades"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-child--student--grades', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-child--student--grades"
          onclick="tryItOut('GETapi-v1-parent-child--student--grades');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-child--student--grades"
          onclick="cancelTryOut('GETapi-v1-parent-child--student--grades');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-child--student--grades"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/child/{student}/grades</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-child--student--grades"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-child--student--grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-child--student--grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>student</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="student"        data-endpoint="GETapi-v1-parent-child--student--grades"
        value="architecto"
        data-component="url">
  <br>
<p>Example: <code>architecto</code></p>
      </div>
          </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-child--student--attendance">Get Child Attendance</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil 30 absensi harian terakhir anak.</p>

<span id="example-requests-GETapi-v1-parent-child--student--attendance">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/child/architecto/attendance" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/child/architecto/attendance"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-child--student--attendance">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-child--student--attendance" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-child--student--attendance"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-child--student--attendance"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-child--student--attendance" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-child--student--attendance">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-child--student--attendance" data-method="GET"
   data-path="api/v1/parent/child/{student}/attendance"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-child--student--attendance', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-child--student--attendance"
          onclick="tryItOut('GETapi-v1-parent-child--student--attendance');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-child--student--attendance"
          onclick="cancelTryOut('GETapi-v1-parent-child--student--attendance');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-child--student--attendance"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/child/{student}/attendance</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-child--student--attendance"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-child--student--attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-child--student--attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>student</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="student"        data-endpoint="GETapi-v1-parent-child--student--attendance"
        value="architecto"
        data-component="url">
  <br>
<p>Example: <code>architecto</code></p>
      </div>
          </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-child--student--billings">Get Child Billings</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar tagihan keuangan anak.</p>

<span id="example-requests-GETapi-v1-parent-child--student--billings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/child/architecto/billings" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/child/architecto/billings"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-child--student--billings">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-child--student--billings" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-child--student--billings"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-child--student--billings"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-child--student--billings" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-child--student--billings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-child--student--billings" data-method="GET"
   data-path="api/v1/parent/child/{student}/billings"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-child--student--billings', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-child--student--billings"
          onclick="tryItOut('GETapi-v1-parent-child--student--billings');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-child--student--billings"
          onclick="cancelTryOut('GETapi-v1-parent-child--student--billings');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-child--student--billings"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/child/{student}/billings</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-child--student--billings"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-child--student--billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-child--student--billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>student</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="student"        data-endpoint="GETapi-v1-parent-child--student--billings"
        value="architecto"
        data-component="url">
  <br>
<p>Example: <code>architecto</code></p>
      </div>
          </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-child--student--points">Get Child BK Points</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil catatan log poin BK anak.</p>

<span id="example-requests-GETapi-v1-parent-child--student--points">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/child/architecto/points" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/child/architecto/points"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-child--student--points">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-child--student--points" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-child--student--points"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-child--student--points"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-child--student--points" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-child--student--points">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-child--student--points" data-method="GET"
   data-path="api/v1/parent/child/{student}/points"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-child--student--points', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-child--student--points"
          onclick="tryItOut('GETapi-v1-parent-child--student--points');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-child--student--points"
          onclick="cancelTryOut('GETapi-v1-parent-child--student--points');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-child--student--points"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/child/{student}/points</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-child--student--points"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-child--student--points"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-child--student--points"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>student</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="student"        data-endpoint="GETapi-v1-parent-child--student--points"
        value="architecto"
        data-component="url">
  <br>
<p>Example: <code>architecto</code></p>
      </div>
          </form>

          <h2 id="parent-scoped-data-v1-GETapi-v1-parent-messages">Get Messages Chat History</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar obrolan pesan antara Orang Tua dan Guru terkait anak-anak mereka.</p>

<span id="example-requests-GETapi-v1-parent-messages">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/parent/messages" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/messages"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-parent-messages">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-parent-messages" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-parent-messages"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-parent-messages"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-parent-messages" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-parent-messages">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-parent-messages" data-method="GET"
   data-path="api/v1/parent/messages"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-parent-messages', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-parent-messages"
          onclick="tryItOut('GETapi-v1-parent-messages');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-parent-messages"
          onclick="cancelTryOut('GETapi-v1-parent-messages');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-parent-messages"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/parent/messages</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-parent-messages"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-parent-messages"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-parent-messages"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="parent-scoped-data-v1-POSTapi-v1-parent-messages">Send Message to Teacher</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengirim pesan konsultasi baru ke guru tertentu.
Memicu pengiriman WebSocket real-time <code>ChatMessageSent</code> secara internal.</p>

<span id="example-requests-POSTapi-v1-parent-messages">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/parent/messages" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json" \
  --data "{
  \"recipient_id\": 5,
  \"student_id\": 3,
  \"message\": \"Assalamu\'alaikum wr. wb.\"
}"
</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/parent/messages"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};

let body = {
  "recipient_id": 5,
  "student_id": 3,
  "message": "Assalamu'alaikum wr. wb."
};

fetch(url, {
  method: "POST",
  headers,
  body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-parent-messages">
</span>
<span id="execution-results-POSTapi-v1-parent-messages" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-parent-messages"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-parent-messages"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-parent-messages" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-parent-messages">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-parent-messages" data-method="POST"
   data-path="api/v1/parent/messages"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-parent-messages', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-parent-messages"
          onclick="tryItOut('POSTapi-v1-parent-messages');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-parent-messages"
          onclick="cancelTryOut('POSTapi-v1-parent-messages');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-parent-messages"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/parent/messages</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-parent-messages"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-parent-messages"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-parent-messages"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
    <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>recipient_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="recipient_id"        data-endpoint="POSTapi-v1-parent-messages"
        value="5"
        data-component="body">
  <br>
<p>ID Guru (User ID). Example: <code>5</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>student_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="student_id"        data-endpoint="POSTapi-v1-parent-messages"
        value="3"
        data-component="body">
  <br>
<p>ID Siswa (Anak). Example: <code>3</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>message</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="message"        data-endpoint="POSTapi-v1-parent-messages"
        value="Assalamu'alaikum wr. wb."
        data-component="body">
  <br>
<p>Isi teks pesan obrolan. Example: <code>Assalamu'alaikum wr. wb.</code></p>
    </div>
    </form>

        <h1 id="student-scoped-data-v1">Student Scoped Data v1</h1>

  <p>API endpoint khusus untuk peran Siswa guna melihat data akademik, jadwal, kehadiran, BK, dan keuangan mereka.</p>

                <h2 id="student-scoped-data-v1-GETapi-v1-student-profile">Get Student Profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mendapatkan profil lengkap akademik diri sendiri.</p>

<span id="example-requests-GETapi-v1-student-profile">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/profile" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/profile"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-profile">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-profile" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-profile"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-profile"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-profile" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-profile" data-method="GET"
   data-path="api/v1/student/profile"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-profile', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-profile"
          onclick="tryItOut('GETapi-v1-student-profile');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-profile"
          onclick="cancelTryOut('GETapi-v1-student-profile');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-profile"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/profile</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-profile"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-profile"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-profile"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-schedule">Get Student Schedule</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil jadwal pelajaran siswa untuk tahun akademik saat ini berdasarkan kelasnya.</p>

<span id="example-requests-GETapi-v1-student-schedule">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/schedule" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/schedule"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-schedule">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-schedule" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-schedule"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-schedule"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-schedule" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-schedule">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-schedule" data-method="GET"
   data-path="api/v1/student/schedule"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-schedule', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-schedule"
          onclick="tryItOut('GETapi-v1-student-schedule');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-schedule"
          onclick="cancelTryOut('GETapi-v1-student-schedule');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-schedule"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/schedule</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-schedule"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-schedule"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-schedule"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-grades">Get Student Grades</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil riwayat nilai (tugas, UH, PTS, PAS) siswa.</p>

<span id="example-requests-GETapi-v1-student-grades">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/grades" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/grades"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-grades">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-grades" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-grades"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-grades"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-grades" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-grades">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-grades" data-method="GET"
   data-path="api/v1/student/grades"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-grades', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-grades"
          onclick="tryItOut('GETapi-v1-student-grades');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-grades"
          onclick="cancelTryOut('GETapi-v1-student-grades');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-grades"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/grades</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-grades"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-grades"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-attendance">Get Student Attendance</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil riwayat 30 kehadiran harian terakhir milik siswa.</p>

<span id="example-requests-GETapi-v1-student-attendance">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/attendance" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/attendance"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-attendance">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-attendance" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-attendance"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-attendance"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-attendance" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-attendance">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-attendance" data-method="GET"
   data-path="api/v1/student/attendance"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-attendance', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-attendance"
          onclick="tryItOut('GETapi-v1-student-attendance');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-attendance"
          onclick="cancelTryOut('GETapi-v1-student-attendance');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-attendance"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/attendance</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-attendance"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-billings">Get Student Billings</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil seluruh rincian tagihan keuangan sekolah siswa.</p>

<span id="example-requests-GETapi-v1-student-billings">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/billings" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/billings"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-billings">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-billings" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-billings"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-billings"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-billings" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-billings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-billings" data-method="GET"
   data-path="api/v1/student/billings"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-billings', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-billings"
          onclick="tryItOut('GETapi-v1-student-billings');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-billings"
          onclick="cancelTryOut('GETapi-v1-student-billings');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-billings"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/billings</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-billings"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-billings"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-points">Get Student BK Points</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil rekam jejak poin BK (prestasi atau pelanggaran) siswa.</p>

<span id="example-requests-GETapi-v1-student-points">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/points" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/points"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-points">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-points" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-points"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-points"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-points" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-points">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-points" data-method="GET"
   data-path="api/v1/student/points"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-points', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-points"
          onclick="tryItOut('GETapi-v1-student-points');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-points"
          onclick="cancelTryOut('GETapi-v1-student-points');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-points"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/points</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-points"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-points"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-points"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-GETapi-v1-student-notifications">Get Student Notifications</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar notifikasi in-app aktif khusus siswa.</p>

<span id="example-requests-GETapi-v1-student-notifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/student/notifications" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/notifications"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-student-notifications">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-student-notifications" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-student-notifications"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-student-notifications"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-student-notifications" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-student-notifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-student-notifications" data-method="GET"
   data-path="api/v1/student/notifications"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-student-notifications', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-student-notifications"
          onclick="tryItOut('GETapi-v1-student-notifications');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-student-notifications"
          onclick="cancelTryOut('GETapi-v1-student-notifications');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-student-notifications"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/student/notifications</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-student-notifications"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-student-notifications"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-student-notifications"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="student-scoped-data-v1-POSTapi-v1-student-notifications--id--read">Mark Notification as Read</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Menandai notifikasi tertentu sebagai telah dibaca.</p>

<span id="example-requests-POSTapi-v1-student-notifications--id--read">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/student/notifications/architecto/read" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/student/notifications/architecto/read"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "POST",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-student-notifications--id--read">
</span>
<span id="execution-results-POSTapi-v1-student-notifications--id--read" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-student-notifications--id--read"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-student-notifications--id--read"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-student-notifications--id--read" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-student-notifications--id--read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-student-notifications--id--read" data-method="POST"
   data-path="api/v1/student/notifications/{id}/read"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-student-notifications--id--read', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-student-notifications--id--read"
          onclick="tryItOut('POSTapi-v1-student-notifications--id--read');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-student-notifications--id--read"
          onclick="cancelTryOut('POSTapi-v1-student-notifications--id--read');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-student-notifications--id--read"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/student/notifications/{id}/read</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-student-notifications--id--read"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-student-notifications--id--read"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-student-notifications--id--read"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
          <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="id"        data-endpoint="POSTapi-v1-student-notifications--id--read"
        value="architecto"
        data-component="url">
  <br>
<p>The ID of the notification. Example: <code>architecto</code></p>
      </div>
          </form>

        <h1 id="teacher-scoped-data-v1">Teacher Scoped Data v1</h1>

  <p>API endpoint khusus untuk peran Guru guna melihat jadwal mengajar, menginput absensi kelas, dan membuat jurnal mengajar.</p>

                <h2 id="teacher-scoped-data-v1-GETapi-v1-teacher-schedule">Get Teacher Schedule</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil jadwal mengajar guru pada tahun akademik saat ini.</p>

<span id="example-requests-GETapi-v1-teacher-schedule">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/teacher/schedule" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/teacher/schedule"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-teacher-schedule">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-teacher-schedule" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-teacher-schedule"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-teacher-schedule"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-teacher-schedule" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-teacher-schedule">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-teacher-schedule" data-method="GET"
   data-path="api/v1/teacher/schedule"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-teacher-schedule', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-teacher-schedule"
          onclick="tryItOut('GETapi-v1-teacher-schedule');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-teacher-schedule"
          onclick="cancelTryOut('GETapi-v1-teacher-schedule');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-teacher-schedule"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/teacher/schedule</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-teacher-schedule"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-teacher-schedule"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-teacher-schedule"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="teacher-scoped-data-v1-GETapi-v1-teacher-classes">Get Taught Classrooms</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar seluruh kelas yang diajar oleh guru tersebut.</p>

<span id="example-requests-GETapi-v1-teacher-classes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/teacher/classes" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/teacher/classes"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-teacher-classes">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-teacher-classes" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-teacher-classes"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-teacher-classes"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-teacher-classes" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-teacher-classes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-teacher-classes" data-method="GET"
   data-path="api/v1/teacher/classes"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-teacher-classes', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-teacher-classes"
          onclick="tryItOut('GETapi-v1-teacher-classes');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-teacher-classes"
          onclick="cancelTryOut('GETapi-v1-teacher-classes');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-teacher-classes"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/teacher/classes</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-teacher-classes"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-teacher-classes"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-teacher-classes"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="teacher-scoped-data-v1-POSTapi-v1-teacher-attendance">Store Bulk Attendance</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Menginput absensi kehadiran siswa secara massal untuk kelas tertentu.</p>

<span id="example-requests-POSTapi-v1-teacher-attendance">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/teacher/attendance" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json" \
  --data "{
  \"classroom_id\": 2,
  \"date\": \"2026-05-27\",
  \"schedule_id\": 4,
  \"students\": [
    \"architecto\"
  ]
}"
</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/teacher/attendance"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};

let body = {
  "classroom_id": 2,
  "date": "2026-05-27",
  "schedule_id": 4,
  "students": [
    "architecto"
  ]
};

fetch(url, {
  method: "POST",
  headers,
  body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-teacher-attendance">
</span>
<span id="execution-results-POSTapi-v1-teacher-attendance" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-teacher-attendance"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-teacher-attendance"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-teacher-attendance" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-teacher-attendance">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-teacher-attendance" data-method="POST"
   data-path="api/v1/teacher/attendance"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-teacher-attendance', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-teacher-attendance"
          onclick="tryItOut('POSTapi-v1-teacher-attendance');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-teacher-attendance"
          onclick="cancelTryOut('POSTapi-v1-teacher-attendance');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-teacher-attendance"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/teacher/attendance</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
    <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>classroom_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="classroom_id"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="2"
        data-component="body">
  <br>
<p>ID Kelas. Example: <code>2</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="date"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="2026-05-27"
        data-component="body">
  <br>
<p>Tanggal absensi (YYYY-MM-DD). Example: <code>2026-05-27</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>schedule_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="schedule_id"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="4"
        data-component="body">
  <br>
<p>optional ID Jadwal terkait. Example: <code>4</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
    <details>
      <summary style="padding-bottom: 10px;">
        <b style="line-height: 2;"><code>students</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>List data absensi per siswa.</p>
      </summary>
                        <div style="margin-left: 14px; clear: unset;">
            <b style="line-height: 2;"><code>student_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="students.0.student_id"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="architecto"
        data-component="body">
  <br>
<p>The <code>id</code> of an existing record in the students table. Example: <code>architecto</code></p>
          </div>
                                <div style="margin-left: 14px; clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="students.0.status"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="A"
        data-component="body">
  <br>
<p>Example: <code>A</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>H</code></li> <li><code>S</code></li> <li><code>I</code></li> <li><code>A</code></li></ul>
          </div>
                                <div style="margin-left: 14px; clear: unset;">
            <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="students.0.notes"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="n"
        data-component="body">
  <br>
<p>validation.max. Example: <code>n</code></p>
          </div>
                                <div style=" margin-left: 14px; clear: unset;">
    <details>
      <summary style="padding-bottom: 10px;">
        <b style="line-height: 2;"><code>*</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>

      </summary>
                        <div style="margin-left: 28px; clear: unset;">
            <b style="line-height: 2;"><code>student_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="students.*.student_id"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="3"
        data-component="body">
  <br>
<p>ID Siswa. Example: <code>3</code></p>
          </div>
                                <div style="margin-left: 28px; clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="students.*.status"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="H"
        data-component="body">
  <br>
<p>Status absensi (H, S, I, A). Example: <code>H</code></p>
          </div>
                                <div style="margin-left: 28px; clear: unset;">
            <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="students.*.notes"        data-endpoint="POSTapi-v1-teacher-attendance"
        value="Sakit flu ringan"
        data-component="body">
  <br>
<p>optional Catatan tambahan absensi. Example: <code>Sakit flu ringan</code></p>
          </div>
                  </details>
    </div>
                    </details>
    </div>
    </form>

          <h2 id="teacher-scoped-data-v1-GETapi-v1-teacher-journals">Get Teaching Journals</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil riwayat pengisian jurnal mengajar guru.</p>

<span id="example-requests-GETapi-v1-teacher-journals">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/teacher/journals" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/teacher/journals"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-teacher-journals">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-teacher-journals" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-teacher-journals"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-teacher-journals"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-teacher-journals" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-teacher-journals">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-teacher-journals" data-method="GET"
   data-path="api/v1/teacher/journals"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-teacher-journals', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-teacher-journals"
          onclick="tryItOut('GETapi-v1-teacher-journals');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-teacher-journals"
          onclick="cancelTryOut('GETapi-v1-teacher-journals');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-teacher-journals"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/teacher/journals</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-teacher-journals"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-teacher-journals"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-teacher-journals"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="teacher-scoped-data-v1-POSTapi-v1-teacher-journals">Store Teaching Journal</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Membuat entri baru jurnal mengajar digital beserta lampiran pendukung.</p>

<span id="example-requests-POSTapi-v1-teacher-journals">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request POST \
  "https://sistem-terpadu.test/api/v1/teacher/journals" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: multipart/form-data" \
  --header "Accept: application/json" \
  --form "subject_id=3"\
  --form "classroom_id=2"\
  --form "teaching_date=2026-05-27"\
  --form "jam_ke=1"\
  --form "topic=Aljabar Linier"\
  --form "learning_activity=Ceramah dan tanya jawab"\
  --form "learning_method=Diskusi Kelompok"\
  --form "obstacles=Beberapa proyektor mati"\
  --form "notes=Perlu review materi aljabar"\
  --form "students_present=32"\
  --form "students_absent=2"\
  --form "attachment=@C:\Users\kemalhafizh\AppData\Local\Temp\phpB2CA.tmp" </code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/teacher/journals"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "multipart/form-data",
  "Accept": "application/json",
};

const body = new FormData();
body.append('subject_id', '3');
body.append('classroom_id', '2');
body.append('teaching_date', '2026-05-27');
body.append('jam_ke', '1');
body.append('topic', 'Aljabar Linier');
body.append('learning_activity', 'Ceramah dan tanya jawab');
body.append('learning_method', 'Diskusi Kelompok');
body.append('obstacles', 'Beberapa proyektor mati');
body.append('notes', 'Perlu review materi aljabar');
body.append('students_present', '32');
body.append('students_absent', '2');
body.append('attachment', document.querySelector('input[name="attachment"]').files[0]);

fetch(url, {
  method: "POST",
  headers,
  body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-teacher-journals">
</span>
<span id="execution-results-POSTapi-v1-teacher-journals" hidden>
  <blockquote>Received response<span
        id="execution-response-status-POSTapi-v1-teacher-journals"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-POSTapi-v1-teacher-journals"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-teacher-journals" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-POSTapi-v1-teacher-journals">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-teacher-journals" data-method="POST"
   data-path="api/v1/teacher/journals"
   data-authed="1"
   data-hasfiles="1"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-teacher-journals', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-POSTapi-v1-teacher-journals"
          onclick="tryItOut('POSTapi-v1-teacher-journals');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-POSTapi-v1-teacher-journals"
          onclick="cancelTryOut('POSTapi-v1-teacher-journals');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-POSTapi-v1-teacher-journals"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-black">POST</small>
      <b><code>api/v1/teacher/journals</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="POSTapi-v1-teacher-journals"
        value="multipart/form-data"
        data-component="header">
  <br>
<p>Example: <code>multipart/form-data</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="POSTapi-v1-teacher-journals"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
    <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="subject_id"        data-endpoint="POSTapi-v1-teacher-journals"
        value="3"
        data-component="body">
  <br>
<p>ID Mata Pelajaran. Example: <code>3</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>classroom_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="classroom_id"        data-endpoint="POSTapi-v1-teacher-journals"
        value="2"
        data-component="body">
  <br>
<p>ID Kelas. Example: <code>2</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>teaching_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="teaching_date"        data-endpoint="POSTapi-v1-teacher-journals"
        value="2026-05-27"
        data-component="body">
  <br>
<p>Tanggal mengajar (YYYY-MM-DD). Example: <code>2026-05-27</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>jam_ke</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="jam_ke"        data-endpoint="POSTapi-v1-teacher-journals"
        value="1"
        data-component="body">
  <br>
<p>Jam keberapa pengajaran. Example: <code>1</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>topic</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="topic"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Aljabar Linier"
        data-component="body">
  <br>
<p>Topik atau materi pelajaran. Example: <code>Aljabar Linier</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>learning_activity</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="learning_activity"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Ceramah dan tanya jawab"
        data-component="body">
  <br>
<p>Aktivitas KBM berlangsung. Example: <code>Ceramah dan tanya jawab</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>learning_method</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="learning_method"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Diskusi Kelompok"
        data-component="body">
  <br>
<p>Metode KBM. Example: <code>Diskusi Kelompok</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>obstacles</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="obstacles"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Beberapa proyektor mati"
        data-component="body">
  <br>
<p>optional Hambatan KBM jika ada. Example: <code>Beberapa proyektor mati</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>notes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="notes"        data-endpoint="POSTapi-v1-teacher-journals"
        value="Perlu review materi aljabar"
        data-component="body">
  <br>
<p>optional Catatan guru. Example: <code>Perlu review materi aljabar</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>students_present</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="students_present"        data-endpoint="POSTapi-v1-teacher-journals"
        value="32"
        data-component="body">
  <br>
<p>Jumlah siswa hadir. Example: <code>32</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>students_absent</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
        <input type="number" style="display: none"
        step="any"        name="students_absent"        data-endpoint="POSTapi-v1-teacher-journals"
        value="2"
        data-component="body">
  <br>
<p>Jumlah siswa tidak hadir. Example: <code>2</code></p>
    </div>
        <div style=" padding-left: 28px; clear: unset;">
      <b style="line-height: 2;"><code>attachment</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
        <input type="file" style="display: none"
               name="attachment"        data-endpoint="POSTapi-v1-teacher-journals"
        value=""
        data-component="body">
  <br>
<p>optional File PDF/Gambar lampiran jurnal (max 10MB). Example: <code>C:\Users\kemalhafizh\AppData\Local\Temp\phpB2CA.tmp</code></p>
    </div>
    </form>

        <h1 id="universal-mobile-data-v1">Universal Mobile Data v1</h1>

  <p>API endpoint umum untuk seluruh pengguna aplikasi mobile guna mengambil pengumuman, agenda kalender akademik, dan notifikasi.</p>

                <h2 id="universal-mobile-data-v1-GETapi-v1-announcements">Get Announcements</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar 20 pengumuman aktif sekolah terbaru yang didelegasikan untuk tipe peran Anda.</p>

<span id="example-requests-GETapi-v1-announcements">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/announcements" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/announcements"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-announcements">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-announcements" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-announcements"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-announcements"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-announcements" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-announcements">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-announcements" data-method="GET"
   data-path="api/v1/announcements"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-announcements', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-announcements"
          onclick="tryItOut('GETapi-v1-announcements');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-announcements"
          onclick="cancelTryOut('GETapi-v1-announcements');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-announcements"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/announcements</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-announcements"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-announcements"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-announcements"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="universal-mobile-data-v1-GETapi-v1-calendar">Get Calendar Events</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil daftar 50 agenda kalender akademik aktif sekolah.</p>

<span id="example-requests-GETapi-v1-calendar">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/calendar" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/calendar"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-calendar">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-calendar" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-calendar"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-calendar"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-calendar" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-calendar">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-calendar" data-method="GET"
   data-path="api/v1/calendar"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-calendar', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-calendar"
          onclick="tryItOut('GETapi-v1-calendar');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-calendar"
          onclick="cancelTryOut('GETapi-v1-calendar');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-calendar"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/calendar</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-calendar"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-calendar"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-calendar"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

          <h2 id="universal-mobile-data-v1-GETapi-v1-notifications">Get Notifications</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil seluruh riwayat notifikasi in-app pengguna.</p>

<span id="example-requests-GETapi-v1-notifications">
<blockquote>Example request:</blockquote>


<div class="bash-example">
  <pre><code class="language-bash">curl --request GET \
  --get "https://sistem-terpadu.test/api/v1/notifications" \
  --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
  --header "Content-Type: application/json" \
  --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
  <pre><code class="language-javascript">const url = new URL(
  "https://sistem-terpadu.test/api/v1/notifications"
);

const headers = {
  "Authorization": "Bearer {YOUR_AUTH_KEY}",
  "Content-Type": "application/json",
  "Accept": "application/json",
};


fetch(url, {
  method: "GET",
  headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-notifications">
      <blockquote>
      <p>Example response (401):</p>
    </blockquote>
        <details class="annotation">
      <summary style="cursor: pointer;">
        <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
      </summary>
      <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=(self)
content-security-policy: default-src &#039;self&#039;; script-src &#039;self&#039; &#039;unsafe-inline&#039; &#039;unsafe-eval&#039; https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; style-src &#039;self&#039; &#039;unsafe-inline&#039; https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src &#039;self&#039; https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src &#039;self&#039; data: https:; frame-src &#039;self&#039; https://app.midtrans.com https://sandbox.midtrans.com; connect-src &#039;self&#039; ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;
access-control-allow-origin: *
 </code></pre></details>     <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
  </span>
<span id="execution-results-GETapi-v1-notifications" hidden>
  <blockquote>Received response<span
        id="execution-response-status-GETapi-v1-notifications"></span>:
  </blockquote>
  <pre class="json"><code id="execution-response-content-GETapi-v1-notifications"
   data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-notifications" hidden>
  <blockquote>Request failed with error:</blockquote>
  <pre><code id="execution-error-message-GETapi-v1-notifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-notifications" data-method="GET"
   data-path="api/v1/notifications"
   data-authed="1"
   data-hasfiles="0"
   data-isarraybody="0"
   autocomplete="off"
   onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-notifications', this);">
  <h3>
    Request&nbsp;&nbsp;&nbsp;
          <button type="button"
          style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-tryout-GETapi-v1-notifications"
          onclick="tryItOut('GETapi-v1-notifications');">Try it out 
      </button>
      <button type="button"
          style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-canceltryout-GETapi-v1-notifications"
          onclick="cancelTryOut('GETapi-v1-notifications');" hidden>Cancel 
      </button>&nbsp;&nbsp;
      <button type="submit"
          style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
          id="btn-executetryout-GETapi-v1-notifications"
          data-initial-text="Send Request "
          data-loading-text=" Sending..."
          hidden>Send Request 
      </button>
      </h3>
      <p>
      <small class="badge badge-green">GET</small>
      <b><code>api/v1/notifications</code></b>
    </p>
        <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Authorization" class="auth-value"        data-endpoint="GETapi-v1-notifications"
        value="Bearer {YOUR_AUTH_KEY}"
        data-component="header">
  <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Content-Type"        data-endpoint="GETapi-v1-notifications"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
                <div style="padding-left: 28px; clear: unset;">
        <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
        <input type="text" style="display: none"
               name="Accept"        data-endpoint="GETapi-v1-notifications"
        value="application/json"
        data-component="header">
  <br>
<p>Example: <code>application/json</code></p>
      </div>
            </form>

      

    
  </div>
  <div class="dark-box">
          <div class="lang-selector">
                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
              </div>
      </div>
</div>
</body>
</html>
