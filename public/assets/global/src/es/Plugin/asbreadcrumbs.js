import Plugin from 'Plugin'

const NAME = 'breadcrumb'

class Breadcrumb extends Plugin {
  getName() {
    return NAME
  }
  static getDefaults() {
    return {
      overflow: 'left',
      namespace: 'breadcrumb',
      dropdownMenuClass: 'dropdown-menu',
      dropdownItemClass: 'dropdown-item',
      toggleIconClass: 'wb-chevron-down-mini',
      ellipsis(classes, label) {
        return `<li class="breadcrumb-item ${classes.ellipsisClass}">${label}</li>`
      },
      dropdown(classes) {
        const dropdownClass = 'dropdown'
        let dropdownMenuClass = 'dropdown-menu'

        if (this.options.overflow === 'right') {
          dropdownMenuClass += ' dropdown-menu-right'
        }

        return `<li class="breadcrumb-item ${dropdownClass} ${classes.dropdownClass}">
          <a href="javascript:void(0);" class="${classes.toggleClass}" data-toggle="dropdown">
            <i class="${classes.toggleIconClass}"></i>
          </a>
          <div class="${dropdownMenuClass} ${classes.dropdownMenuClass}" role="menu"></div>
        </li>`
      },
      dropdownItem(classes, label, href) {
        if (!href) {
          return `<a class="${classes.dropdownItemClass} ${classes.dropdownItemDisableClass}" href="#">${label}</a>`
        }
        return `<a class="${classes.dropdownItemClass}" href="${href}">${label}</a>`
      }
    }
  }
  render() {
    const $el = this.$el
    $el.asBreadcrumbs(this.options)
  }
}
Plugin.register(NAME, Breadcrumb)

export default Breadcrumb
