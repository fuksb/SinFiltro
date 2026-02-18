<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Página no encontrada | SinFiltros</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡️</text></svg>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
      background: #0a0c14;
      color: #e4e4e7;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    
    .stars {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      pointer-events: none;
      background: 
        radial-gradient(2px 2px at 20px 30px, #fff, transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.8), transparent),
        radial-gradient(1px 1px at 90px 40px, #fff, transparent),
        radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,0.6), transparent),
        radial-gradient(1px 1px at 160px 120px, #fff, transparent);
      background-size: 200px 200px;
      animation: twinkle 8s ease-in-out infinite;
    }
    
    @keyframes twinkle {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    
    .container {
      text-align: center;
      padding: 2rem;
      position: relative;
      z-index: 1;
    }
    
    .error-code {
      font-size: clamp(6rem, 20vw, 12rem);
      font-weight: 800;
      line-height: 1;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 0 80px rgba(168, 85, 247, 0.4);
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    h1 {
      font-size: clamp(1.5rem, 4vw, 2rem);
      margin: 1rem      color: #f4 0;
f4f5;
    }
    
    p {
      font-size: 1.1rem;
      color: #a1a1aa;
      max-width: 500px;
      margin: 0 auto 2rem;
      line-height: 1.6;
    }
    
    .ghost {
      font-size: 4rem;
      margin-bottom: 1rem;
      animation: float 3s ease-in-out infinite;
      display: inline-block;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.875rem 1.75rem;
      font-size: 1rem;
      font-weight: 600;
      color: #fff;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      border: none;
      border-radius: 12px;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
    }
    
    .suggestions {
      margin-top: 3rem;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1rem;
    }
    
    .suggestion-link {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      color: #a1a1aa;
      text-decoration: none;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }
    
    .suggestion-link:hover {
      color: #fff;
      border-color: rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.05);
    }
    
    .suggestion-link i {
      color: #8b5cf6;
    }
    
    .sparkle {
      position: absolute;
      width: 4px;
      height: 4px;
      background: #fff;
      border-radius: 50%;
      animation: sparkle 2s ease-in-out infinite;
    }
    
    @keyframes sparkle {
      0%, 100% { opacity: 0; transform: scale(0); }
      50% { opacity: 1; transform: scale(1); }
    }
    
    .sparkle:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
    .sparkle:nth-child(2) { top: 30%; right: 15%; animation-delay: 0.5s; }
    .sparkle:nth-child(3) { bottom: 25%; left: 20%; animation-delay: 1s; }
    .sparkle:nth-child(4) { bottom: 35%; right: 10%; animation-delay: 1.5s; }
  </style>
</head>
<body>
  <div class="stars"></div>
  
  <div class="container">
    <div class="sparkle"></div>
    <div class="sparkle"></div>
    <div class="sparkle"></div>
    <div class="sparkle"></div>
    
    <div class="ghost">👻</div>
    <div class="error-code">404</div>
    <h1>¡Vaya! Esta página se Lost in the Matrix</h1>
    <p>La página que buscas quizás nunca existió, o quizás fue absorbida por el algoritmo. No te preocupes, puedes volver a casa.</p>
    
    <a href="/" class="btn">
      <i class="fa-solid fa-house"></i>
      Volver al inicio
    </a>
    
    <div class="suggestions">
      <a href="/coche" class="suggestion-link">
        <i class="fa-solid fa-car"></i> Coches
      </a>
      <a href="/hipoteca" class="suggestion-link">
        <i class="fa-solid fa-percent"></i> Hipotecas
      </a>
      <a href="/luz" class="suggestion-link">
        <i class="fa-solid fa-bolt"></i> Luz
      </a>
      <a href="/telco" class="suggestion-link">
        <i class="fa-solid fa-mobile-screen"></i> Teléfono
      </a>
      <a href="/seguros" class="suggestion-link">
        <i class="fa-solid fa-umbrella"></i> Seguros
      </a>
      <a href="/inversiones" class="suggestion-link">
        <i class="fa-solid fa-chart-pie"></i> Inversiones
      </a>
    </div>
  </div>
</body>
</html>
