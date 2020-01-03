import $ from 'jquery';
import Plugin from 'Plugin';

const NAME = 'formatter';

class Formatter extends Plugin {
  getName() {
    return NAME;
  }

  static getDefaults() {
    return {
      persistent: true
    };
  }

  render() {
    if (!$.fn.formatter) {
      return;
    }

    let browserName = navigator.userAgent.toLowerCase(),
      ieOptions;

    if (/msie/i.test(browserName) && !/opera/.test(browserName)) {
      ieOptions = {
        persistent: false
      };
    } else {
      ieOptions = {};
    }

    let $el = this.$el,
      options = this.options;

    if (options.pattern) {
      options.pattern = options.pattern
        .replace(/\[\[/g, '{{')
        .replace(/\]\]/g, '}}');
    }
    $el.formatter(options);
  }
}

Plugin.register(NAME, Formatter);

export default Formatter;
