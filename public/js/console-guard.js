(function () {
    'use strict';

    var warnStyle = 'background:#c0392b;color:#ffffff;font-size:28px;font-weight:900;padding:12px 20px;border-radius:6px;line-height:1.3;';
    var bodyStyle = 'color:#e74c3c;font-size:14px;font-weight:600;line-height:1.6;max-width:520px;';
    var devStyle  = 'color:#7f8c8d;font-size:12px;font-weight:400;line-height:1.5;max-width:520px;';

    console.log('%c\u26a0\ufe0f Stop! Do not proceed!', warnStyle);

    console.log(
        '%cThis is a browser feature intended for developers only. Writing or pasting code here is not for regular users.\n' +
        '\n' +
        'If someone asked you to open this console and paste any code, DO NOT do it. That is a trick used to hijack your account, session and data.\n' +
        '\n' +
        'Never paste anything you do not fully understand. If you are facing a problem, contact your system administrator or the development team instead.',
        bodyStyle
    );

    console.log(
        '%cFor developers: this console is available for debugging this application. Feel free to use it if you know what you are doing.\n' +
        '\n' +
        'Sazzad Ali',
        devStyle
    );
})();