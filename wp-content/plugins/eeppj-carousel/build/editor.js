/**
 * EEPPJ Carousel — Gutenberg Block Editor
 * Uses wp.* globals directly (no build step required)
 */
(function () {
  'use strict';

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useState = wp.element.useState;
  var useEffect = wp.element.useEffect;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var RangeControl = wp.components.RangeControl;
  var ToggleControl = wp.components.ToggleControl;
  var Button = wp.components.Button;
  var Placeholder = wp.components.Placeholder;

  registerBlockType('eeppj/carousel', {
    title: 'EEPPJ Carousel',
    description: 'Carrusel de contenido estilo Apple con controles flotantes y autoplay.',
    icon: 'slides',
    category: 'media',
    keywords: ['carousel', 'slider', 'carrusel', 'apple'],
    attributes: {
      slides: { type: 'array', default: [] },
      autoplayDuration: { type: 'number', default: 5000 },
      showPlayPause: { type: 'boolean', default: true },
      blockId: { type: 'string', default: '' },
    },
    supports: {
      align: ['wide', 'full'],
      html: false,
    },

    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var slides = attributes.slides || [];
      var activeSlide = 0;

      // Generate stable block ID
      useEffect(function () {
        if (!attributes.blockId) {
          setAttributes({ blockId: 'eeppj-carousel-' + Math.random().toString(36).substr(2, 9) });
        }
      }, []);

      function updateSlide(index, fieldOrObj, value) {
        var updated = slides.map(function (s, i) {
          if (i === index) {
            var copy = Object.assign({}, s);
            if (typeof fieldOrObj === 'object') {
              Object.assign(copy, fieldOrObj);
            } else {
              copy[fieldOrObj] = value;
            }
            return copy;
          }
          return s;
        });
        setAttributes({ slides: updated });
      }

      function addSlide() {
        setAttributes({
          slides: slides.concat([{
            headline: '',
            description: '',
            mediaUrl: '',
            mediaId: 0,
            mediaType: 'image',
          }]),
        });
      }

      function removeSlide(index) {
        setAttributes({
          slides: slides.filter(function (_, i) { return i !== index; }),
        });
      }

      function moveSlide(index, direction) {
        var newSlides = slides.slice();
        var target = index + direction;
        if (target < 0 || target >= newSlides.length) return;
        var temp = newSlides[index];
        newSlides[index] = newSlides[target];
        newSlides[target] = temp;
        setAttributes({ slides: newSlides });
      }

      // Inspector: global settings
      var inspector = el(InspectorControls, null,
        el(PanelBody, { title: 'Configuración del Carrusel', initialOpen: true },
          el(RangeControl, {
            label: 'Duración autoplay (ms)',
            value: attributes.autoplayDuration,
            onChange: function (v) { setAttributes({ autoplayDuration: v }); },
            min: 1000,
            max: 15000,
            step: 500,
          }),
          el(ToggleControl, {
            label: 'Mostrar botón Play/Pausa',
            checked: attributes.showPlayPause,
            onChange: function (v) { setAttributes({ showPlayPause: v }); },
          })
        )
      );

      // Empty state
      if (slides.length === 0) {
        return el(Fragment, null,
          inspector,
          el(Placeholder, {
            icon: 'slides',
            label: 'EEPPJ Carousel',
            instructions: 'Agregue diapositivas para crear el carrusel.',
          },
            el(Button, { isPrimary: true, onClick: addSlide }, 'Agregar Diapositiva')
          )
        );
      }

      // Slide editor cards
      var slideCards = slides.map(function (slide, i) {
        return el('div', {
          key: i,
          style: {
            background: '#1d1d1f',
            borderRadius: '12px',
            padding: '20px',
            marginBottom: '12px',
            border: '1px solid rgba(255,255,255,0.08)',
          },
        },
          // Header
          el('div', {
            style: {
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              marginBottom: '16px',
            },
          },
            el('span', {
              style: {
                color: '#f5f5f7',
                fontWeight: '600',
                fontSize: '13px',
                letterSpacing: '0.02em',
              },
            }, 'Diapositiva ' + (i + 1)),
            el('div', { style: { display: 'flex', gap: '4px' } },
              el(Button, {
                isSmall: true,
                icon: 'arrow-up-alt2',
                label: 'Mover arriba',
                onClick: function () { moveSlide(i, -1); },
                disabled: i === 0,
              }),
              el(Button, {
                isSmall: true,
                icon: 'arrow-down-alt2',
                label: 'Mover abajo',
                onClick: function () { moveSlide(i, 1); },
                disabled: i === slides.length - 1,
              }),
              el(Button, {
                isSmall: true,
                isDestructive: true,
                icon: 'trash',
                label: 'Eliminar',
                onClick: function () { removeSlide(i); },
              })
            )
          ),
          // Fields
          el(TextControl, {
            label: 'Titular',
            value: slide.headline || '',
            onChange: function (v) { updateSlide(i, 'headline', v); },
            placeholder: 'Título de la diapositiva',
            style: { marginBottom: '8px' },
          }),
          el(TextareaControl, {
            label: 'Descripción',
            value: slide.description || '',
            onChange: function (v) { updateSlide(i, 'description', v); },
            placeholder: 'Descripción breve',
            rows: 2,
            style: { marginBottom: '12px' },
          }),
          // Media upload
          el(MediaUpload, {
            onSelect: function (media) {
              var type = (media.type || '').startsWith('video') ? 'video' : 'image';
              updateSlide(i, { mediaUrl: media.url, mediaId: media.id, mediaType: type });
            },
            allowedTypes: ['image', 'video'],
            value: slide.mediaId || 0,
            render: function (obj) {
              if (slide.mediaUrl) {
                return el('div', { style: { position: 'relative' } },
                  slide.mediaType === 'video'
                    ? el('video', {
                        src: slide.mediaUrl,
                        style: { width: '100%', borderRadius: '8px', maxHeight: '180px', objectFit: 'cover' },
                        muted: true,
                        autoPlay: true,
                        loop: true,
                      })
                    : el('img', {
                        src: slide.mediaUrl,
                        style: { width: '100%', borderRadius: '8px', maxHeight: '180px', objectFit: 'cover' },
                      }),
                  el('div', { style: { marginTop: '8px', display: 'flex', gap: '8px' } },
                    el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, 'Cambiar'),
                    el(Button, {
                      isDestructive: true,
                      isSmall: true,
                      onClick: function () {
                        updateSlide(i, { mediaUrl: '', mediaId: 0 });
                      },
                    }, 'Quitar')
                  )
                );
              }
              return el(Button, {
                isSecondary: true,
                onClick: obj.open,
                style: { width: '100%', justifyContent: 'center', padding: '20px', border: '1px dashed rgba(255,255,255,0.2)' },
              }, 'Subir Imagen o Video');
            },
          })
        );
      });

      return el(Fragment, null,
        inspector,
        el('div', {
          style: {
            background: '#000',
            borderRadius: '20px',
            padding: '24px',
            fontFamily: "'DM Sans', -apple-system, sans-serif",
          },
        },
          el('div', {
            style: {
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              marginBottom: '20px',
            },
          },
            el('span', {
              style: { color: '#f5f5f7', fontWeight: '700', fontSize: '15px' },
            }, 'Carrusel — ' + slides.length + ' diapositiva' + (slides.length !== 1 ? 's' : '')),
            el(Button, {
              isPrimary: true,
              isSmall: true,
              onClick: addSlide,
              icon: 'plus-alt2',
            }, 'Agregar')
          ),
          slideCards
        )
      );
    },

    save: function () {
      // Server-side rendered
      return null;
    },
  });
})();
