import $ from 'jquery'
import Plugin from 'Plugin'

const NAME = 'editableTable'

class EditableTable extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {}
  }

  render() {
    if (!$.fn.editableTableWidget) {
      return
    }

    const $el = this.$el

    $el.editableTableWidget(this.options)
  }
}

Plugin.register(NAME, EditableTable)

export default EditableTable
