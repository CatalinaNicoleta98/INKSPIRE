<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 Not Found | Inkspire</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    /* Floating moon animation */
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    /* Soft shimmer */
    @keyframes shimmer {
      0% { opacity: 0.7; }
      50% { opacity: 1; }
      100% { opacity: 0.7; }
    }
  </style>

</head>
<body class="bg-gradient-to-b from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center">

  <div class="bg-white p-10 rounded-2xl shadow-xl max-w-md w-full text-center border border-indigo-100 relative overflow-hidden">

    <!-- Soft background glow circles -->
    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-200 rounded-full opacity-30 blur-3xl"></div>
    <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-purple-200 rounded-full opacity-20 blur-3xl"></div>

    <div class="relative z-10">

      <!-- Logo at top -->
      <img src="uploads/logo.png" class="mx-auto w-20 mb-4 opacity-90" alt="Inkspire Logo">

      <!-- Floating moon icon -->
      <div class="text-6xl mb-4 animate-[float_4s_ease-in-out_infinite]">🌙</div>

      <h1 class="text-4xl font-bold text-gray-800 mb-2 animate-[shimmer_4s_ease-in-out_infinite]">404</h1>

      <p class="text-gray-600 mb-6 text-sm leading-relaxed">
        The page you're looking for doesn't exist or has been moved.<br>
        Let’s guide you back to something inspiring.
      </p>

      <a href="index.php?action=home"
         class="inline-block bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 transition shadow-sm">
         ⬅ Go back home
      </a>

      <div class="mt-6 text-xs text-gray-400">
        Inkspire · Created with care ✨
      </div>

    </div>
  </div>

</body>
</html>