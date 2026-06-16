#import "@preview/lilaq:0.6.0" as lq
#import "../theme.typ": *

#let code-metrics-bar-graph(data) = {
  let _ = data.remove("Total")

  lq.diagram(
    width: 95%,
    height: 8cm,
    title: [Code-Metriken],
    xlabel: [Dateityp],
    ylabel: [Zeilen],
    xaxis: (
      ticks: data.keys().map(rotate.with(-30deg, reflow: true)).enumerate(),
      subticks: none,
    ),
    yaxis: (
      format-ticks: (ticks, ..args) => {
        let result = lq.format-ticks-linear(ticks, ..args)
        let suffix = if result.exponent == 3 {
          "k"
        } else if result.exponent == 6 {
          "M"
        } else if result.exponent == 9 {
          "G"
        }
        ticks
          .zip(result.labels)
          .map(((tick, label)) => {
            if tick == 0 {
              label
            } else {
              label + suffix
            }
          })
      },
    ),
    lq.bar(
      range(data.len()),
      data.values().map(it => it.code),
      label: "Codezeilen",
      offset: -0.25,
      width: 0.25,
    ),
    lq.bar(
      range(data.len()),
      data.values().map(it => it.comments),
      label: "Kommentarzeilen",
      width: 0.25,
    ),
    lq.bar(
      range(data.len()),
      data.values().map(it => it.blanks),
      label: "Leerzeilen",
      offset: 0.25,
      width: 0.25,
    ),
  )
}

#let code-metrics-table(data) = {
  if data.len() == 2 {
    let _ = data.remove("Total")
  }
  table(
    columns: 4,
    table.header[*Typ*][*Codezeilen*][*Kommentarzeilen*][*Leerzeilen*],
    ..data
      .pairs()
      .map(((key, value)) => (
        [_#(key)_],
        [#value.code],
        [#value.comments],
        [#value.blanks],
      ))
      .flatten(),
    align: (x, y) => if x == 0 { right } else { auto },
  )
}

#let big-o-n = {
  set text(fill: colors.yellow.rgb)
  show math.equation: math.bold
  $O(n)$
}

#let big-o-1 = {
  set text(fill: colors.green.rgb)
  show math.equation: math.bold
  $O(1)$
}

#let big-o-log-n = {
  set text(fill: colors.green.rgb)
  show math.equation: math.bold
  $O(log(n))$
}

#let big-o-n-log-n = {
  set text(fill: colors.peach.rgb)
  show math.equation: math.bold
  $O(n log(n))$
}

#let big-o-n-squared = {
  set text(fill: colors.red.rgb)
  show math.equation: math.bold
  $O(n^2)$
}

#let big-o-n-cubed = {
  set text(fill: colors.red.rgb)
  show math.equation: math.bold
  $O(n^3)$
}

#let big-o-2-to-the-n = {
  set text(fill: colors.red.rgb)
  show math.equation: math.bold
  $O(2^n)$
}

#let big-o-n-factorial = {
  set text(fill: colors.red.rgb)
  show math.equation: math.bold
  $O(n!)$
}
