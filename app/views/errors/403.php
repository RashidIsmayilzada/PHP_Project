<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
        }

        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">🚫</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            You don't have permission to access this page. This area is restricted to authorized users only.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="/login" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</body>
</html>
