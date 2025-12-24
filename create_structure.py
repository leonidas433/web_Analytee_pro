import os
import base64

# Crear directorios
dirs = ['d:\\analytee-landingv2\\public\\assets']
for d in dirs:
    os.makedirs(d, exist_ok=True)
    print("Creado: " + d)

# 1x1 transparent PNG en base64
transparent_png = base64.b64decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
)

# Crear imágenes placeholder
images = [
    'd:\\analytee-landingv2\\public\\assets\\hero.png',
    'd:\\analytee-landingv2\\public\\assets\\hero.webp'
]

for img in images:
    with open(img, 'wb') as f:
        f.write(transparent_png)
    print("Creada: " + img)

print("Estructura completada")
