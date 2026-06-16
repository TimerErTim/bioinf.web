#import "theme.typ": _theme as theme, catppuccin, colors
#import "libs.typ": *
#import "deps.typ": *

#let format-raw(it) = {
  set text(font: "JetBrains Mono")

  if it.block {
    codly(
      stroke: 1pt + colors.overlay2.rgb,
      fill: colors.mantle.rgb,
      zebra-fill: colors.crust.rgb,
      lang-stroke: it => it.color.lighten(50%),
      smart-skip: true,
      number-align: horizon + right,
      skip-number: align(right)[#sym.dots.v],
      languages: codly-languages,
    )
    it
  } else {
    h(3pt)
    highlight(
      fill: colors.crust.rgb,
      radius: 4pt,
      extent: 3pt,
      it,
    )
    h(3pt)
  }
}

#let format-equation(it) = {
  set text(font: "Libertinus Math")
  it
}

#let format-quote(quote) = {
  let border-color = colors.overlay0.rgb
  if quote.block {
    block(
      stroke: (
        x: border-color,
      ),
      radius: 8pt,
      inset: (y: 8pt),
      above: 1em,
      below: 1em,
    )[
      #quote
    ]
  } else {
    quote
  }
}

#let format-image(it) = {
  it
}

#let format-figure(it) = {
  set align(start)
  show figure.caption: set text(size: 10pt)
  show image: rect.with(inset: 0pt)
  it
}

#let format-link(it) = {
  set text(fill: colors.blue.rgb)
  underline(it, evade: true)
}

#let format-diagrams(doc) = {
  let accent-colors = colors
    .pairs()
    .filter(((name, val)) => val.accent)
    .to-dict()
  let cycle = (
    accent-colors.sky.rgb,
    accent-colors.green.rgb,
    accent-colors.yellow.rgb,
    accent-colors.mauve.rgb,
    accent-colors.red.rgb,
    accent-colors.blue.rgb,
    accent-colors.pink.rgb,
    accent-colors.peach.rgb,
    accent-colors.teal.rgb,
    accent-colors.rosewater.rgb,
    // accent-colors.flamingo.rgb,
    // accent-colors.maroon.rgb,
    // accent-colors.sapphire.rgb,
    // accent-colors.lavender.rgb,
  )
  //let cycle = bit_reverse_shuffle(accent-colors)

  show: lq.set-diagram(cycle: cycle)
  show: lq.set-spine(stroke: colors.overlay0.rgb)
  show: lq.set-tick(stroke: colors.overlay0.rgb)
  show: lq.set-grid(stroke: colors.surface1.rgb)
  show: lq.set-legend(fill: colors.overlay0.rgb.transparentize(75%))
  doc
}

#let documentation-template(
  title: none,
  semester-term: "",
  author: none,
  aufwand-in-h: none,
  student-id: none,
  doc,
) = context {
  let title = title
  if title == none {
    title = document.title
  }
  let author = author
  if author == none {
    author = document.author.join(", ")
  }

  // Global init
  show: catppuccin.with(theme)
  set page(fill: white)
  show: codly-init.with()

  set page(
    footer: context [
      #set align(right)
      #let cur = counter(page).get().first()
      #let tot = counter(page).final().first()
      Seite #cur / #tot
    ],
    header: [
      #semester-term #h(1fr) #author
    ],
  )
  set heading(numbering: "1.1.")
  show heading: set block(below: 1em, above: 1.25em)
  show heading: it => {
    set text(size: 16pt - it.level * 1pt)
    it
  }
  set text(
    font: (
      "Roboto",
      "Arial",
    ),
    lang: "de",
  )

  align(center)[
    #text(17pt)[*#title*]\
    #text(14pt)[#semester-term]

    #if aufwand-in-h != none [
      #text(13pt)[
        Aufwand in h: #aufwand-in-h
      ]
    ]\
    #text(16pt)[#author]\
    #if student-id != none [
      #text(13pt)[
        s#student-id
      ]
    ]
  ]

  context {
    let show_outline = counter(page).final().first() > 5

    if show_outline [
      #show outline.entry: it => [
        #set text(size: 14pt - it.element.level * 1.5pt)
        #it
      ]
      #outline(title: "Inhaltsverzeichnis")
      #pagebreak()
    ] else {
      v(2em)
    }
  }

  set rect(stroke: colors.overlay2.rgb)
  show raw: format-raw
  show quote: format-quote
  show image: format-image
  show math.equation: format-equation
  show figure: format-figure
  show link: format-link
  set table(fill: (x, y) => { if y == 0 { colors.surface0.rgb } })
  show: format-diagrams

  doc
}
