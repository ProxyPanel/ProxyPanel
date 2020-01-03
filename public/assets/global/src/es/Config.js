let values = {
  fontFamily: 'Noto Sans, sans-serif',
  primaryColor: 'blue',
  assets: '../assets'
}

function get(...names) {
  let data = values
  const callback = function (data, name) {
    return data[name]
  }

  for (let i = 0; i < names.length; i++) {
    const name = names[i]

    data = callback(data, name)
  }

  return data
}

function set(name, value) {
  if (typeof name === 'string' && typeof value !== 'undefined') {
    values[name] = value
  } else if (typeof name === 'object') {
    values = $.extend(true, {}, values, name)
  }
}

function getColor(name, level) {
  if (name === 'primary') {
    name = get('primaryColor')
    if (!name) {
      name = 'red'
    }
  }

  if (typeof values.colors === 'undefined') {
    return null
  }

  if (typeof values.colors[name] !== 'undefined') {
    if (level && typeof values.colors[name][level] !== 'undefined') {
      return values.colors[name][level]
    }

    if (typeof level === 'undefined') {
      return values.colors[name]
    }
  }

  return null
}

function colors(name, level) {
  return getColor(name, level)
}

export {
  get,
  set,
  getColor,
  colors
}
