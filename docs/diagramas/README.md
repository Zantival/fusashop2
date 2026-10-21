# Diagramas de FusaShop

Diagramas construidos a partir del estado del proyecto en la rama `Final` y de sus rutas, modelos y migraciones.

## Archivos

| Diagrama | Fuente Mermaid | Imagen PNG |
|---|---|---|
| Casos de uso por rol | `casos-de-uso.mmd` | `png/casos-de-uso.png` |
| UML de clases del dominio | `uml-clases.mmd` | `png/uml-clases.png` |
| UML de componentes y servicios | `uml-componentes.mmd` | `png/uml-componentes.png` |
| Entidad-relación conceptual | `entidad-relacion.mmd` | `png/entidad-relacion.png` |
| Esquema relacional y claves | `modelo-relacional.mmd` | `png/modelo-relacional.png` |

El modelo relacional refleja las claves foráneas y asociaciones persistidas por las migraciones. `notifications` usa el mecanismo polimórfico de Laravel; `p_q_r_s.assigned_to`, `p_q_r_s.merchant_id`, `search_logs.user_id` y el destino de `global_banners.link_url` no tienen restricción FK. Las tablas internas de colas, caché y sesiones se excluyen de los diagramas de negocio para mantenerlos legibles.

## Regenerar los PNG

Desde la raíz del repositorio, con las dependencias de Node instaladas:

```powershell
node scripts/render-mermaid-diagrams.cjs
```

El script lee las fuentes `.mmd` y usa `sharp`, incluido en el proyecto, para exportar PNG con los nodos conectados por sus asociaciones. El diagrama de casos de uso dibuja actores, límite del sistema y casos como óvalos.

