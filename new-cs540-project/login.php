
<?php
    session_start();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Auth - BookSmart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #0052cc;
            --grey: #f5f5f5;
            --text: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Noto Sans", sans-serif;
            background: var(--grey);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 6px;
            width: 100%;
            max-width: 400px;
        }

        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #ddd;
        }

        .tabs button {
            flex: 1;
            border: none;
            background: #fff;
            padding: .75rem;
            cursor: pointer;
            font-weight: 600;
            color: #888;
            transition: color .2s;
        }

        .tabs button.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }

        .panel {
            display: none;
        }

        .panel.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: .25rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: .6rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type=submit] {
            width: 100%;
            background: var(--primary);
            color: #fff;
            border: 0;
            padding: .65rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .link {
            text-align: center;
            margin-top: 1rem;
            font-size: .875rem;
        }
    </style>


    <script type="text/javascript" src="script/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        //加载页面

        $(function () {
            //绑定点击事件，按键提交
            $("#sub-btn").click(function () {

                //校验用户名
                var usernameVal = $("#username").val();
                var usernamePattern = /^\w{5,10}$/;
                if (!usernamePattern.test(usernameVal)) {
                    $("span[class='errorMsg']").text("Please enter 5–10 characters or digits")
                    return false;
                }

                //校验邮箱
                var emailVal = $("#email").val();
                var emailPattern = /^[A-Za-z0-9.%+_]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/
                if (!emailPattern.test(emailVal)) {
                    $("span[class='errorMsg']").text("Please enter a valid email format")
                    return false;
                }
                //校验电话
                var phoneNumVal = $("#phonenumber").val();
                var phoneNumPattern = /^\d{10}$|^\d{12}$/
                if (!phoneNumPattern.test(phoneNumVal)) {
                    $("span[class='errorMsg']").text("Please enter a valid phoneNum format")
                    return false;
                }

                //校验密码
                var passwordVal = $("#pwd").val();
                var passwordPattern = /^\w{6,10}$/;
                if (!passwordPattern.test(passwordVal)) {
                    $("span[class='errorMsg']").text("Please enter 6–10 characters or digits password")
                    return false;
                }
                //检验密码一致性

                var password2Val = $("#pwd2").val();
                if (passwordVal!=password2Val) {
                    $("span[class='errorMsg']").text("Please enter the same pwd")
                    return false;
                }



               $("span.errorMsg").text("Validation successful")

               return true;

            })

        })
    </script>


</head>


<body>
<div class="card">
    <!-- 切换标签 -->
    <div class="tabs">
        <button id="loginTab" class="active" onclick="switchPanel('login')">Login</button>
        <button id="regTab" onclick="switchPanel('reg')">Register</button>
    </div>

    <!-- 登录面板 -->
    <div id="loginPanel" class="panel active">

        <span style="font-size: 10pt; font-weight: bold; float: right; color: red">${requestScope.msg}</span>


        <form id="loginForm" action="memberServlet" method="post">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Username</label>
                <input type="username" name="username" value="${requestScope.username}" id="username-login" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>

    <!-- 注册面板 -->
    <div id="regPanel" class="panel">

        <span class="errorMsg"
              style="float: right; font-weight: bold; font-size: 12pt; color: red; margin-left: 10px"></span>

        <form id="registerForm" action="memberServlet" method="post">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>Username</label>
                <input type="username" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="phonenumber" name="phonenumber" id="phonenumber">
            </div>

            <!-- <div class="form-group">
                <label>Address</label>
                <input type="address" name="address" id="address">
            </div> -->

            <div class="form-group">
                <label>Password (≥6 characters)</label>
                <input type="password" id="pwd" name="password" minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="pwd2" name="confirmPassword" minlength="6">
            </div>
            <button type="submit" id="sub-btn">Create Account</button>
        </form>
    </div>
</div>

<script>
    function switchPanel(type) {
        const loginPanel = document.getElementById('loginPanel');
        const regPanel = document.getElementById('regPanel');
        const loginTab = document.getElementById('loginTab');
        const regTab = document.getElementById('regTab');

        if (type === 'login') {
            loginPanel.classList.add('active');
            regPanel.classList.remove('active');
            loginTab.classList.add('active');
            regTab.classList.remove('active');
        } else {
            loginPanel.classList.remove('active');
            regPanel.classList.add('active');
            loginTab.classList.remove('active');
            regTab.classList.add('active');
        }
    }
</script>


</body>
</html>