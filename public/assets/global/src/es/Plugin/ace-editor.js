import Plugin from 'Plugin'

const NAME = 'ace'

class AceEditor extends Plugin {
  getName() {
    return NAME
  }

  render() {
    if (typeof ace === 'undefined') {
      return
    }
    // ace.config.set("themePath", "../theme");
    ace.config.loadModule('ace/ext/language_tools')

    const $el = this.$el

    const id = $el.attr('id')

    const editor = ace.edit(id)

    editor.container.style.opacity = ''
    if (this.options.mode) {
      editor.session.setMode(`ace/mode/${this.options.mode}`)
    }
    if (this.options.theme) {
      editor.setTheme(`ace/theme/${this.options.theme}`)
    }

    editor.setOption('maxLines', 40)
    editor.setAutoScrollEditorIntoView(true)

    ace.config.loadModule('ace/ext/language_tools', () => {
      editor.setOptions({
        enableSnippets: true,
        enableBasicAutocompletion: true
      })
    })
  }
}

Plugin.register(NAME, AceEditor)

export default AceEditor
