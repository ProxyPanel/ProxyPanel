import Plugin from 'Plugin'

const NAME = 'strength'

class Strength extends Plugin {
  getName() {
    return NAME
  }

  static getDefaults() {
    return {
      showMeter: true,
      showToggle: false,

      templates: {
        toggle: '<div class="checkbox-custom checkbox-primary show-password-wrap"><input type="checkbox" class="{toggleClass}" title="Show/Hide Password" id="show_password" /><label for="show_password">Show Password</label></div>',
        meter: '<div class="{meterClass}">{score}</div>',
        score: '<div class="{scoreClass}"></div>',
        main: '<div class="{containerClass}">{input}{meter}{toggle}</div>'
      },

      classes: {
        container: 'strength-container',
        status: 'strength-{status}',
        input: 'strength-input',
        toggle: 'strength-toggle',
        meter: 'strength-meter',
        score: 'strength-score'
      },

      scoreLables: {
        invalid: 'Invalid',
        weak: 'Weak',
        good: 'Good',
        strong: 'Strong'
      },

      scoreClasses: {
        invalid: 'strength-invalid',
        weak: 'strength-weak',
        good: 'strength-good',
        strong: 'strength-strong'
      }
    }
  }
}

Plugin.register(NAME, Strength)

export default Strength
