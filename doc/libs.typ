#import "deps.typ": *

#let collect_by_key(list, key-fn) = {
  let dict = (:)
  for item in list {
    let key-value = str(key-fn(item))
    if key-value not in dict {
      dict.insert(key-value, ())
    }
    dict.at(key-value).push(item)
  }
  return dict
}

#let map_dict_values(dict_, map-fn) = {
  return dict_.pairs().map(((key, value)) => (key, map-fn(value))).to-dict()
}

#let apply-nullsafe(value, fn) = {
  if value == none {
    return none
  }
  return fn(value)
}

#let null-default(value, default) = {
  if value == none {
    return default
  }
  return value
}

#let pintora-diagram(text) = {
  box(clip: true, inset: -1mm, pintorita-neo.render(text))
}
