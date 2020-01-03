import $ from 'jquery'
import Component from 'Component'
import {
  getPluginAPI,
  pluginFactory
} from 'Plugin'

export default class extends Component {
  initializePlugins(context = false) {
    $('[data-plugin]', context || this.$el).each(function () {
      const $this = $(this)


      const name = $this.data('plugin')


      const plugin = pluginFactory(name, $this, $this.data())

      if (plugin) {
        plugin.initialize()
      }
    })
  }

  initializePluginAPIs(context = document) {
    const apis = getPluginAPI()

    for (const name in apis) {
      getPluginAPI(name)(`[data-plugin=${name}]`, context)
    }
  }
}
