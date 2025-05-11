<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>iTiket</title>
        <link rel="stylesheet" type="text/css" href="./style/style_bg.css"/>
        <link rel="stylesheet" type="text/css" href="./style/style_in.css"/>
        <link rel="icon" type="image/png" href="./image/itiket_logo.png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      </head>
    <body>
        <header>
          <div class="logo">
            <a href="./index.php">
              <img class="headerlogo" src="./image/itiket_logo.png"/>
              <img class="headertext" src="./image/itiket_text.png"/>
            </a>
          </div>
          <div class="togglemode" aria-label="Toggle theme">
          <label class="container">
            <input checked="checked" type="checkbox" id="theme-toggle"/>
            <div class="checkmark"></div>
            <div class="torch">
              <div class="head">
                <div class="face top">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>
                <div class="face left">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>
                <div class="face right">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>
              </div>
              <div class="stick">
                <div class="side side-left">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>
                <div class="side side-right">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>
              </div>
            </div>
          </label>
        </div>
          <div class="menu">
          <nav>
            <ul>
              <li>
                <a href="./html/services.html">SERVICES</a>
              </li>
              <hr/>
              <li>
                <a href="./html/features.html">FEATURES</a>
              </li>
              <hr/>
              <li>
                <a href="./html/updates.html">UPDATES</a>
              </li>
              <hr/>
              <li>
                <a href="./ticketing.php">TICKET</a>
              </li>
            </ul>
          </nav>
          </div>
          <div class="login">
            <button class="button">
              <a href="./login.php">
                <div class="text">
                  Login
                </div>
              </a>
            </button>
            <button class="button">
              <a href="./registro.php">
                <div class="text">
                  Sign Up
                </div>
              </a>
            </button>
          </div>
        </header>
        <main>
          <section class="hero">
              <div class="hero-content">
                  <h1>La solución perfecta para la gestión de tickets</h1>
                  <p>iTiket simplifica la gestión de tickets de soporte, mejorando la comunicación entre equipos y clientes.</p>
                  <a href="./html/prices.html"> 
		    <button class="cta-button">Empieza ahora</button>
              	  </a>
              </div>
              <div class="hero-image">
                  <img class="imgs" src="./image/panel.jpeg" alt="Dashboard iTiket"/>
              </div>
          </section>
          
          <section class="features">
              <h2>Características principales</h2>
              <div class="features-grid">
                  <div class="feature-card">
                      <img src="./image/ticketing.jpeg" alt="Gestión de tickets"/>
                      <h3>Gestión de Tickets</h3>
                      <p>Sistema intuitivo para crear, asignar y seguir tickets.</p>
                  </div>
                  <div class="feature-card">
                      <img src="./image/panel.jpeg" alt="Panel de control"/>
                      <h3>Panel de Control</h3>
                      <p>Visualiza métricas importantes en tiempo real.</p>
                  </div>
                  <div class="feature-card">
                      <img src="./image/equipo.jpeg" alt="Colaboración en equipo"/>
                      <h3>Colaboración</h3>
                      <p>Trabaja en equipo para resolver problemas eficientemente.</p>
                  </div>
              </div>
          </section>
          
          <!-- Reemplaza tu sección de carrusel con esto -->
          <div class="carousel-container">
            <h2>Nuestros clientes confían en nosotros</h2>
            <div class="carousel" data-items="6"> <!-- Cambia el número según tus imágenes -->
                <div aria-hidden="true" class="group">
                    <div class="card"><img src="./image/foto1.jpeg" class="imagecard" alt="Cliente 1" loading="lazy"></div>
                    <div class="card"><img src="./image/foto2.jpeg" class="imagecard" alt="Cliente 2" loading="lazy"></div>
                    <div class="card"><img src="./image/foto3.jpeg" class="imagecard" alt="Cliente 3" loading="lazy"></div>
                    <div class="card"><img src="./image/foto4.jpeg" class="imagecard" alt="Cliente 4" loading="lazy"></div>
                    <div class="card"><img src="./image/foto5.jpeg" class="imagecard" alt="Cliente 5" loading="lazy"></div>
                    <div class="card"><img src="./image/foto6.jpeg" class="imagecard" alt="Cliente 6" loading="lazy"></div>
                </div>
            </div>
          </div>
          
          <section class="testimonials">
              <h2>Testimonios</h2>
              <div class="testimonial-slider">
                  
              </div>
          </section>
          
	        <section class="discord-cta">
    		    <div class="discord-content"> 
       	 	    <h2>Únete a nuestra comunidad en Discord</h2>
        	    <p>Conéctate con otros usuarios, obtén soporte directo y mantente al día con las últimas novedades.</p>
	            <a href="https://discord.gg/fUuu5WAuRF" target="_blank" rel="noopener noreferrer" class="discord-button">
            	  <img src="./image/docu2.png" alt="Discord Logo"/>
            	  Unirse al Discord
        	    </a>
	    	    </div>
    	      <div class="discord-image">
        	    <img src="./image/index2.jpg" alt="Comunidad Discord"/>
    	      </div>
	        </section>
      </main>
      
      <footer>
        <div class="footer-container">
            <div class="footer-logo-section">
                <div class="footer-logo">
                    <img src="./image/itiket_logo.png" alt="iTiket Logo"/>
                    <span class="footer-logo-text">iTiket</span>
                </div>
                <p class="footer-description">
                    La solución completa para la gestión de tickets y atención al cliente. 
                    Simplifica tus procesos y mejora la experiencia de tus usuarios.
                </p>
            </div>
            
            <div class="footer-links-section">
                <h3>Producto</h3>
                <ul class="footer-links">
                    <li><a href="./html/features.html">Características</a></li>
                    <li><a href="./html/prices.html">Precios</a></li>
                    <li><a href="./html/services.html">Servicios</a></li>
                    <li><a href="./html/updates.html">Actualizaciones</a></li>
                </ul>
            </div>
            
            <div class="footer-links-section">
                <h3>Recursos</h3>
                <ul class="footer-links">
                    <li><a href="./html/document.html">Documentación</a></li>
                    <li><a href="./html/help.html">Centro de ayuda</a></li>
                    <li><a href="./html/features.html">Características</a></li>
                    <li><a href="./html/updates.html">Blog</a></li>
                </ul>
            </div>
            
            <div class="footer-links-section">
                <h3>Empresa</h3>
                <ul class="footer-links">
                    <li><a href="./html/aboutus.html">Sobre nosotros</a></li>
                    <li><a href="./html/work.html">Trabaja con nosotros</a></li>
                    <li><a href="./html/contact.html">Contacto</a></li>
                    <li><a href="./html/terms.html">Términos y condiciones</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="copyright">© 2025 iTiket. Todos los derechos reservados.</p>
            <div class="social-links">
                <a href="#" class="social-link" aria-label="Facebook">
                    <img src="./image/facebook.png" alt="Facebook"/>
                </a>
                <a href="#" class="social-link" aria-label="Twitter">
                    <img src="./image/twitter.png" alt="Twitter"/>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn">
                    <img src="./image/linkedin.png" alt="LinkedIn"/>
                </a>
                <a href="#" class="social-link" aria-label="Instagram">
                    <img src="./image/instagram.png" alt="Instagram"/>
                </a>
            </div>
        </div>
      </footer>
      
      <script src="./JS/script_bg.js"></script>
      <script src="./JS/script_in.js"></script>
  </body>
</html>
