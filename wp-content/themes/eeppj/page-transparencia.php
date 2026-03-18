<?php
/**
 * Template Name: Transparencia
 *
 * Transparency mega-page — Ley 1712 / Resolución 1519 compliance
 * Data is hardcoded (legally fixed 10 sections that rarely change).
 *
 * @package eeppj
 */

defined('ABSPATH') || exit;

get_header();

$sections = [
    [
        'number' => '1',
        'title'  => 'Información de la entidad',
        'items'  => [
            ['label' => 'Misión, visión, funciones y deberes', 'url' => '/nuestraempresa', 'type' => 'page'],
            ['label' => 'Estructura orgánica – Organigrama', 'url' => '/images/Organigrama-empresa-publicas-de-Jerico.webp', 'type' => 'document'],
            ['label' => 'Mapas y cartas descriptivas de los procesos', 'url' => '/documents/mapa-de-procesos-ESP.ppt', 'type' => 'document'],
            ['label' => 'Directorio Institucional', 'url' => '/contacto', 'type' => 'page'],
            ['label' => 'Directorio de servidores públicos, empleados o contratistas', 'url' => '/documents/RELACION-DEL-PERSONAL-CON-LA-ESCALA-SALARIAL.docx', 'type' => 'document'],
            ['label' => 'Directorio de entidades', 'url' => '/directorio-de-entidades', 'type' => 'page'],
            ['label' => 'Servicio al Público – Acueducto', 'url' => '/acueducto', 'type' => 'page'],
            ['label' => 'Servicio al Público – Alcantarillado', 'url' => '/alcantarillado', 'type' => 'page'],
            ['label' => 'Servicio al Público – Aseo y Sostenibilidad', 'url' => '/aseo', 'type' => 'page'],
            ['label' => 'Normativa', 'url' => '/normatividad', 'type' => 'page'],
            ['label' => 'Formularios', 'url' => '/peticiones-quejas-reclamos-y-recursos', 'type' => 'page'],
            ['label' => 'Mecanismo de presentación directa de solicitudes, quejas y reclamos', 'url' => '/peticiones-quejas-reclamos-y-recursos', 'type' => 'page'],
            ['label' => 'Calendario de actividades y eventos', 'url' => '/calendario-de-actividades-y-eventos', 'type' => 'page'],
            ['label' => 'Información sobre decisiones que puede afectar al público', 'url' => '/blog', 'type' => 'page'],
            ['label' => 'Entes y autoridades que lo vigilan', 'url' => '/entes-y-autoridades-que-vigilan-a-la-entidad', 'type' => 'page'],
        ],
    ],
    [
        'number' => '2',
        'title'  => 'Normativa',
        'items'  => [
            ['label' => 'Leyes', 'url' => '/normatividad', 'type' => 'page'],
            ['label' => 'Decreto Único Reglamentario', 'url' => '/normatividad', 'type' => 'page'],
            ['label' => 'Normativa aplicable', 'url' => '/normatividad', 'type' => 'page'],
            ['label' => 'Vínculo al diario o gaceta oficial', 'url' => 'https://www.imprenta.gov.co/diario-oficial', 'type' => 'external'],
            ['label' => 'Políticas, lineamientos y manuales', 'url' => '/documents/MANUAL-CONTRATACION.pdf', 'type' => 'document'],
            ['label' => 'Sistema Único de Información – SUIN', 'url' => 'https://www.suin-juriscol.gov.co/legislacion/normatividad.html', 'type' => 'external'],
        ],
    ],
    [
        'number' => '3',
        'title'  => 'Contratación',
        'items'  => [
            ['label' => 'Publicación de la información contractual (SECOP)', 'url' => 'https://community.secop.gov.co/Public/Tendering/ContractNoticeManagement/Index?currentLanguage=es-CO&Page=login&Country=CO&SkinName=CCE', 'type' => 'external'],
            ['label' => 'Publicación de la ejecución de los contratos', 'url' => '/documents/INFORMACION-CONTRATOS-2025.xlsx', 'type' => 'document'],
            ['label' => 'Manual de contratación, adquisición y/o compras', 'url' => '/documents/MANUAL-CONTRATACION.pdf', 'type' => 'document'],
        ],
    ],
    [
        'number' => '4',
        'title'  => 'Planeación, Presupuesto e Informes',
        'items'  => [
            ['label' => 'Presupuesto general de ingresos, gastos e inversión', 'url' => '/documents/PRESUPUESTO-2025.pdf', 'type' => 'document'],
            ['label' => 'Ejecución presupuestal', 'url' => '/documents/EJECUCION-PRESUPUESTAL.xlsx', 'type' => 'document'],
            ['label' => 'Plan de Acción', 'url' => '/documents/PLAN-DE-ACCION-EEPPJ-2025.xlsx', 'type' => 'document'],
            ['label' => 'Informes de empalme', 'url' => '/documents/ACTA-DE-EMPALME.pdf', 'type' => 'document'],
            ['label' => 'Información pública y/o relevante', 'url' => '/blog', 'type' => 'page'],
            ['label' => 'Informes de gestión, evaluación y auditoría', 'url' => '/documents/INFORME-RENDICION-DE-CUENTA-CONTRALORIA.pdf', 'type' => 'document'],
            ['label' => 'Informes de la Oficina de Control Interno', 'url' => '/informes-de-la-oficina-de-control-interno', 'type' => 'page'],
            ['label' => 'Informes trimestrales sobre acceso a información, quejas y reclamos', 'url' => '/informes-trimestrales-sobre-acceso-a-informacion-quejas-y-reclamos', 'type' => 'page'],
        ],
    ],
    [
        'number' => '5',
        'title'  => 'Trámites y servicios',
        'items'  => [
            ['label' => 'Trámites y servicios', 'url' => '/guia-de-documentos', 'type' => 'page'],
        ],
    ],
    [
        'number' => '6',
        'title'  => 'Participa',
        'items'  => [
            ['label' => 'Menú Participa', 'url' => '/participa', 'type' => 'page'],
        ],
    ],
    [
        'number' => '7',
        'title'  => 'Datos abiertos',
        'items'  => [
            ['label' => 'Instrumentos de gestión de la información', 'url' => '/datos-abiertos', 'type' => 'page'],
            ['label' => 'Sección de datos abiertos', 'url' => 'https://www.datos.gov.co/', 'type' => 'external'],
        ],
    ],
    [
        'number' => '8',
        'title'  => 'Información específica para grupos de interés',
        'items'  => [
            ['label' => 'Información para niños, niñas y adolescentes', 'url' => '/images/ninos-2.webp', 'type' => 'document'],
            ['label' => 'Información para mujeres', 'url' => '/images/mujeres-2.webp', 'type' => 'document'],
        ],
    ],
    [
        'number' => '9',
        'title'  => 'Información tributaria en entidades territoriales locales',
        'items'  => [],
    ],
    [
        'number' => '10',
        'title'  => 'Certificados y políticas',
        'items'  => [
            ['label' => 'Certificado de Accesibilidad Web', 'url' => '/documents/Certificado-de-accesibilidad-web.pdf', 'type' => 'document'],
            ['label' => 'Política de Seguridad Digital', 'url' => '/documents/POLITICA-DE-SEGURIDAD-DIGITAL-Y-DECLARACION-DE-ADOPCION-DEL-MSPI.pdf', 'type' => 'document'],
        ],
    ],
];
?>

<div style="background: rgba(140,192,75,0.05); border-bottom: 1px solid rgba(140,192,75,0.1);">
  <div class="max-w-7xl px-4" style="padding-top: 2rem; padding-bottom: 2rem; margin-left: auto; margin-right: auto;" class="transp-hero">
    <h1 style="font-size: 1.875rem; font-family: var(--font-heading); font-weight: 700; color: var(--color-brand-blue-dark);" class="transp-title">
      Transparencia y Acceso a la Información Pública
    </h1>
    <p style="color: var(--color-text-muted); margin-top: 0.5rem;">Resolución 1519 de 2020 — Ley 1712 de 2014</p>
  </div>
</div>

<div class="max-w-4xl px-4" style="padding-top: 2rem; padding-bottom: 3rem; margin-left: auto; margin-right: auto;">
  <div class="space-y-6">
    <?php foreach ($sections as $section) : ?>
      <details class="transp-section" <?php echo ($section['number'] === '1') ? 'open' : ''; ?>>
        <summary class="transp-summary">
          <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 9999px; background: var(--color-brand-green); color: #fff; font-size: 0.875rem; font-weight: 700; flex-shrink: 0;">
            <?php echo esc_html($section['number']); ?>
          </span>
          <span style="font-size: 1.125rem;"><?php echo esc_html($section['title']); ?></span>
          <svg class="transp-chevron" style="width: 1.25rem; height: 1.25rem; margin-left: auto; color: #9ca3af; transition: transform 0.15s; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </summary>
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f3f4f6;">
          <?php if (!empty($section['items'])) : ?>
            <?php get_template_part('template-parts/document-list', null, ['items' => $section['items']]); ?>
          <?php else : ?>
            <p style="font-size: 0.875rem; color: var(--color-text-muted); font-style: italic;">Información pendiente de publicación.</p>
          <?php endif; ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
</div>

<style>
  .transp-section { border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; }
  .transp-summary {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem; background: var(--color-surface-muted);
    cursor: pointer; font-family: var(--font-heading); font-weight: 700;
    color: var(--color-brand-blue-dark); user-select: none;
    transition: background-color 0.15s; list-style: none;
  }
  .transp-summary::-webkit-details-marker { display: none; }
  .transp-summary::marker { display: none; content: ''; }
  .transp-summary:hover { background: rgba(140,192,75,0.05); }
  details[open] .transp-chevron { transform: rotate(180deg); }

  @media (min-width: 768px) {
    .transp-hero { padding-top: 3rem; padding-bottom: 3rem; }
    .transp-title { font-size: 2.25rem; }
  }
</style>

<?php get_footer(); ?>
