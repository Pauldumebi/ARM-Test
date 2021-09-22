<!doctype html>
<html>

<head>
  <meta name="viewport" content="width=device-width">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <script src="https://kit.fontawesome.com/c4b75b0548.js" crossorigin="anonymous"></script>
  <title>Verify Email</title>
  <style>
    /* -------------------------------------
        INLINED WITH htmlemail.io/inline
    ------------------------------------- */
    /* -------------------------------------
        RESPONSIVE AND MOBILE FRIENDLY STYLES
    ------------------------------------- */
    @media only screen and (max-width: 620px) {
      table[class=body] h1 {
        font-size: 28px !important;
        margin-bottom: 10px !important;
      }

      table[class=body] p,
      table[class=body] ul,
      table[class=body] ol,
      table[class=body] td,
      table[class=body] span,
      table[class=body] a {
        font-size: 16px !important;
      }

      table[class=body] .wrapper,
      table[class=body] .article {
        padding: 10px !important;
      }

      table[class=body] .content {
        padding: 0 !important;
      }

      table[class=body] .container {
        padding: 0 !important;
        width: 100% !important;
      }

      table[class=body] .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }

      table[class=body] .btn table {
        width: 100% !important;
      }

      table[class=body] .btn a {
        width: 100% !important;
      }

      table[class=body] .img-responsive {
        height: auto !important;
        max-width: 100% !important;
        width: auto !important;
      }
    }

    /* -------------------------------------
        PRESERVE THESE STYLES IN THE HEAD
    ------------------------------------- */
    @media all {
      .w-100 {
        width: 100%;
        border: 1px solid black;
      }

      .ExternalClass {
        width: 100%;
      }

      .ExternalClass,
      .ExternalClass p,
      .ExternalClass span,
      .ExternalClass font,
      .ExternalClass td,
      .ExternalClass div {
        line-height: 100%;
      }

      .apple-link a {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        text-decoration: none !important;
      }

      #MessageViewBody a {
        color: inherit;
        text-decoration: none;
        font-size: inherit;
        font-family: inherit;
        font-weight: inherit;
        line-height: inherit;
      }

      .btn-primary table td:hover {
        background-color: #34495e !important;
      }

      .btn-primary a:hover {
        background-color: #34495e !important;
        border-color: #34495e !important;
      }
    }
  </style>
</head>

<body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
  <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; width: 100%; background-color: #f6f6f6;">
    <tr>
      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
      <td class="container" style="font-family: sans-serif; font-size: 14px; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
        <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

          <!-- START CENTERED WHITE CONTAINER -->
          <table class="main" style="border-collapse: separate; width: 100%; background: #ffffff; border-radius: 3px;">
            <div style="text-align: center;">
              <img src=https://nimdee.com/images/nimdee-02 1.svg" alt="Nimdee-logo" title="Logo" width="auto" height="auto" style="width: 15rem; margin-top: 1rem;" />
            </div>

            <!-- START MAIN CONTENT AREA -->
            <tr>
              <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
                <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; width: 100%;">
                  <tr>
                    <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Almost done {{ $details['name'] }}, to complete your account with CCLearning Platform, we need you to verify your email address: {{ $details['email'] }}</p>
                      <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
                        <tbody>
                          <tr>
                            <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
                              <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto; margin: auto;">
                                <tbody>
                                  <tr>
                                    <div style="text-align: center;">
                                      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #3498db; border-radius: 5px; text-align: center;"> <a href="{{ $details['link'] }}" target="_blank" style="display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;">Verify Email Address</a> </td>
                                    </div>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Once verified, you can access your resources on CCLearning Platform.</p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">Button not working? Paste the following link into your browser: {{ $details['link'] }} </p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">You’re receiving this email because you recently created a new account or added a new email address on our CCLearning platform. If this wasn’t you, please ignore this email.</p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0;">Regards,</p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0;">Funke Olajide</p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0;">Customer Services Executive</p>
                      <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0;">Website: <span><a href="{{ $details['websiteLink'] }}">CCLearning Platform</a></span></p>
                      <div class="w-100" style=" Margin-bottom: 10px;Margin-top: 10px;"></div>
                      <div style="text-align: center;">
                        <span class="apple-link" style="text-align: center;"><a href="https://twitter.com/nimdee2" target="blank" style="text-decoration: underline; text-align: center; width: 1.8rem;"><img style="width: 1.5rem;" src="https://blog.nimdee.com/wp-content/uploads/2021/03/Twitter.png" alt=""></a></span>
                        <span class="apple-link" style="text-align: center;"><a href="http://9ijakids.com" target="blank" style="text-decoration: underline; text-align: center; width: 1.8rem;"><img style="width: 1.5rem;" src="https://blog.nimdee.com/wp-content/uploads/2021/03/facebook.png" alt=""></a></span>
                        <span class="apple-link" style="text-align: center;"><a href="http://9ijakids.com" target="blank" style="text-decoration: underline; text-align: center; width: 1.8rem;"><img style="width: 1.5rem;" src="https://blog.nimdee.com/wp-content/uploads/2021/03/Linkedin.png" alt=""></a></span>
                        <span class="apple-link" style="text-align: center;"><a href="https://api.whatsapp.com/send/?phone=2349033854783&text&app_absent=0" target="blank" style="text-decoration: underline; text-align: center; width: 1.8rem;"><img style="width: 1.5rem;" src="https://blog.nimdee.com/wp-content/uploads/2021/03/Whatsapp.png" alt=""></a></span>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- END MAIN CONTENT AREA -->
          </table>

          <!-- START FOOTER -->
          <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
              <tr>
                <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center; display: flex;">
                </td>
              </tr>
              <tr>
              </tr>
            </table>
          </div>
          <!-- END FOOTER -->

          <!-- END CENTERED WHITE CONTAINER -->
        </div>
      </td>
      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
    </tr>
  </table>
</body>

</html>