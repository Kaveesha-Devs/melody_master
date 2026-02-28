<?php
header('Content-Type: image/svg+xml');
$w = $_GET['w'] ?? 400;
$h = $_GET['h'] ?? 400;
$text = $_GET['text'] ?? 'Product';
echo "<svg xmlns='http://www.w3.org/2000/svg' width='$w' height='$h' viewBox='0 0 $w $h'>
  <rect width='100%' height='100%' fill='#f0f4f8'/>
  <text x='50%' y='45%' font-family='Arial' font-size='16' fill='#adb5bd' text-anchor='middle'>🎸</text>
  <text x='50%' y='60%' font-family='Arial' font-size='12' fill='#adb5bd' text-anchor='middle'>$text</text>
</svg>";
