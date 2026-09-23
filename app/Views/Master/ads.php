<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ads: Top-Left & Right-Bottom</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }
    .ad-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
      width: 100%;
    }
    .top-ad, .bottom-ad {
      width: 100%;
      height: 100px;
      background-color: #ffcc00; /* Ad background color */
      text-align: center;
      line-height: 100px;
      font-weight: bold;
      color: #000;
    }
    .middle-container {
      display: flex;
      flex: 1;
    }
    .left-ad, .right-ad {
      width: 150px;
      background-color: #00ccff; /* Ad background color */
      color: #fff;
      text-align: center;
      writing-mode: vertical-lr;
      line-height: 1.5;
      padding: 10px;
      font-weight: bold;
    }
    .content {
      flex: 1;
      padding: 20px;
      background-color: #f9f9f9;
      overflow: auto;
    }
  </style>
</head>
<body>
  <div class="ad-container">
     
    <!--<div class="top-ad">-->
    <!--  Top Ad-->
    <!--</div>-->

    <!-- Middle Container (Left Ad + Content + Right Ad) -->
    <div class="middle-container">
      <!-- Left Ad -->
      <div class="left-ad">
        Left Ad
      </div>

      <!-- Content Section -->
      <div class="content">
        <h1>Page Content</h1>
        <p>This is the main content area where your content resides. Ads are displayed on the top, left, right, and bottom sides of the page.</p>
        <p>Replace this content with your actual webpage details.</p>
      </div>

      <!-- Right Ad -->
      <!--<div class="right-ad">-->
      <!--  Right Ad-->
      <!--</div>-->
    </div>

    <!-- Bottom Ad -->
    <div class="bottom-ad">
      Bottom Ad
    </div>
  </div>
</body>
</html>
