# Analytee Landing Page - Ready to Deploy

## Project Structure

```
analytee-landingv2/
├── index.html                    # Landing page principal
├── backend/
│   ├── send-mail.php            # Manejador del formulario
│   └── smtp-config.php          # Configuración SMTP
├── css/
│   └── main.min.css             # Estilos compilados
├── images/
│   └── logo.png                 # Logo de la marca
├── public/assets/               # Imágenes del hero
├── politica-privacidad.html     # Página de política
├── terminos-condiciones.html    # Página de términos
├── aviso-legal.html             # Página de avisos legales
├── .env                         # Configuración (git-ignored)
├── .htaccess                    # Configuración Apache
└── sitemap.xml                  # Mapa del sitio
```

## What's Included

✅ **Landing Page** - Completamente funcional con:
  - Hero section con gradientes
  - Secciones de contenido
  - Formulario de contacto
  - Dark mode
  - Internacionalización (i18n)
  - Google Analytics

✅ **Formulario de Contacto** - Funcional con:
  - Validación cliente/servidor
  - Envío SMTP a contacto@analytee.com
  - Confirmación modal
  - Manejo de errores

✅ **Políticas Legales** - 3 páginas:
  - Política de Privacidad
  - Términos y Condiciones
  - Aviso Legal

## What's Removed

❌ Dashboard administrativo  
❌ Sistema de blog  
❌ Base de datos (SQL)  
❌ Scripts de desarrollo  
❌ npm dependencies  
❌ Documentación (excepto este archivo)  
❌ Archivos temporales

## How to Deploy

### Option 1: Apache/PHP Server (Recommended)

```bash
# 1. Upload all files to your web server
# 2. Ensure .env file contains correct SMTP credentials
# 3. Verify /backend/send-mail.php is executable
# 4. Test form submission
```

### Option 2: Docker

```dockerfile
FROM php:8.1-apache
COPY . /var/www/html
RUN a2enmod rewrite
EXPOSE 80
```

## Configuration

Update `.env` with your SMTP settings:

```env
SMTP_HOST=smtp.ionos.es
SMTP_PORT=465
SMTP_USER=your-email@domain.com
SMTP_PASS=your-password
SMTP_FROM_EMAIL=no-reply@domain.com
SMTP_FROM_NAME=Your Company Name
```

## Key Files

| File | Purpose | Size |
|------|---------|------|
| index.html | Landing page | 116 KB |
| main.min.css | Styles | 46 KB |
| send-mail.php | Form handler | 3.6 KB |
| logo.png | Branding | 3.5 KB |

**Total Size: <200 KB** (excluding dependencies)

## Testing

1. **Landing Page**: Open `index.html` in browser
2. **Form**: Fill out contact form and submit
3. **Check Email**: Verify email received at configured address
4. **Check Links**: Verify all internal links work
5. **Dark Mode**: Toggle theme with button
6. **Mobile**: Test responsive design

## Security Notes

⚠️ **Important:**
- Never commit `.env` file to version control
- Keep SMTP credentials secure
- Validate all form inputs on server side
- Use HTTPS in production
- Review server logs for form submissions

## Performance

- **LCP**: <1.5s (with optimized images)
- **CLS**: <0.1 (no layout shifts)
- **FID**: <100ms (interactive)
- **CSS**: 46 KB minified
- **JS**: Inline + external CDNs only

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Maintenance

- Update Google Analytics ID in index.html if needed
- Keep SMTP credentials current in .env
- Monitor form submissions in server logs
- Backup .env file regularly

---

**Last Updated**: 2025-12-02  
**Status**: Ready for Production  
**Requires**: PHP 7.4+ with SMTP capabilities
