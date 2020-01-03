import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'inputGroupFile'

class InputGroupFile extends Plugin {
  getName() {
    return NAME
  }
  render() {
    this.$file = this.$el.find('[type=file]')
    this.$text = this.$el.find('.form-control')
  }
  change() {
    let value = ''
    $.each(this.$file[0].files, (i, file) => {
      value += `${file.name}, `
    })

    value = value.substring(0, value.length - 2)

    this.$text.val(value)
  }
  static api() {
    return 'change|change'
  }
}

Plugin.register(NAME, InputGroupFile)
export default InputGroupFile
