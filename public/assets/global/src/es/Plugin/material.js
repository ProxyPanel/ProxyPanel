import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'formMaterial'

function isChar(e) {
  if (typeof e.which === 'undefined') {
    return true
  } else if (typeof e.which === 'number' && e.which > 0) {
    return !e.ctrlKey && !e.metaKey && !e.altKey && e.which !== 8 && e.which !== 9
  }
  return false
}

class FormMaterial extends Plugin {
  getName() {
    return NAME
  }

  render() {
    const $el = this.$el

    const $control = this.$control = $el.find('.form-control')

    // Add hint label if required
    if ($control.attr('data-hint')) {
      $control.after(`<div class=hint>${$control.attr('data-hint')}</div>`)
    }

    if ($el.hasClass('floating')) {
      // Add floating label if required
      if ($control.hasClass('floating-label')) {
        const placeholder = $control.attr('placeholder')
        $control.attr('placeholder', null).removeClass('floating-label')
        $control.after(`<div class=floating-label>${placeholder}</div>`)
      }

      // Set as empty if is empty
      if (
        $control.val() === null ||
        $control.val() === 'undefined' ||
        $control.val() === ''
      ) {
        $control.addClass('empty')
      }
    }

    // Support for file input
    if ($control.next().is('[type=file]')) {
      $el.addClass('form-material-file')
    }

    this.$file = $el.find('[type=file]')
    this.bind()
    $el.data('formMaterialAPI', this)
  }

  bind() {
    const $el = this.$el
    const $control = this.$control = $el.find('.form-control')

    $el
      .on('keydown.site.material paste.site.material', '.form-control', (e) => {
        if (isChar(e)) {
          $control.removeClass('empty')
        }
      })
      .on('keyup.site.material change.site.material', '.form-control', () => {
        if (
          $control.val() === '' &&
          (typeof $control[0].checkValidity !== 'undefined' &&
            $control[0].checkValidity())
        ) {
          $control.addClass('empty')
        } else {
          $control.removeClass('empty')
        }
      })

    if (this.$file.length > 0) {
      this.$file
        .on('focus', () => {
          this.$el.find('input').addClass('focus')
        })
        .on('blur', () => {
          this.$el.find('input').removeClass('focus')
        })
        .on('change', function () {
          const $this = $(this)
          let value = ''

          $.each($this[0].files, (i, file) => {
            value += `${file.name}, `
          })
          value = value.substring(0, value.length - 2)
          if (value) {
            $this.prev().removeClass('empty')
          } else {
            $this.prev().addClass('empty')
          }
          $this.prev().val(value)
        })
    }
  }
}

Plugin.register(NAME, FormMaterial)

export default FormMaterial
