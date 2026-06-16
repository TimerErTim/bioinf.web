#import "../components.typ": *

#import "@preview/cetz:0.3.4": canvas, draw, tree
#import "@preview/suiji:0.4.0": *
#import "@preview/lilaq:0.6.0" as lq
#import "@preview/tiptoe:0.3.1"

#let gcd_step(a, b) = {
  let t = b
  b = calc.rem(a, b)
  a = t
  (a, b)
}

#let gcd_steps(a, b) = {
  let steps = (
    ((a, b),)
      + while b != 0 {
        (a, b) = gcd_step(a, b)
        ((a, b),)
      }
  )
  steps
}

#let gcd_steps_needed(a, b) = {
  gcd_steps(a, b).len() - 1
}

#let plots_gcd_divergence(a, b, color: auto) = {
  let steps = gcd_steps(a, b)

  let line-plots = ()
  for i in range(1, steps.len()) {
    let from = steps.at(i - 1)
    let to = steps.at(i)
    line-plots.push(lq.line(
      from,
      to,
      stroke: color,
      tip: tiptoe.stealth,
      toe: tiptoe.bar,
    ))
  }

  let start = steps.at(0)
  let text-location = lq.vec.add(start, (-4, 1))
  line-plots.push(lq.place(
    text-location.at(0),
    text-location.at(1),
    align: right,
    pad(
      rest: 5%,
    )[Start (#start.at(0), #start.at(1)) #sym.arrow.r #steps.last().at(0)],
  ))
  line-plots.push(lq.line(
    start,
    text-location,
    stroke: red.transparentize(75%),
  ))

  line-plots
}

#let visualize_gcd_divergence(sample-pairs) = {
  let color-map = lq.color.map.okabe-ito
  let line-plots = for (i, (a, b)) in sample-pairs.enumerate() {
    plots_gcd_divergence(a, b, color: color-map.at(calc.rem(
      i,
      color-map.len(),
    )))
  }

  lq.diagram(
    width: 75%,
    height: 8cm,
    title: [Divergenz des Euklidischen Algorithmus],
    xlabel: [$a$],
    ylabel: lq.label([$b$], angle: 0deg),
    xlim: (0, auto),
    ylim: (0, auto),
    ..line-plots,
  )
}

#let visualize_gcd_divergence_distribution(a-range, b-range) = {
  let x-end = a-range.reduce(calc.max)
  let y-end = b-range.reduce(calc.max)
  let size = calc.max(x-end, y-end) + 1

  let data = for a in range(size) {
    (
      for b in range(size) {
        (0,)
      },
    )
  }

  for a in a-range {
    for b in b-range {
      let steps = gcd_steps(a, b)
      for step in steps {
        data.at(step.at(1)).at(step.at(0)) += 1
      }
    }
  }

  let force-field = lq.quiver(
    a-range,
    b-range,
    (a, b) => {
      if b == 0 {
        return (0, 0)
      }

      let next_gcd = gcd_step(a, b)
      let step = lq.vec.subtract(next_gcd, (a, b))
      // normalize
      lq.vec.multiply(step, 1 / calc.norm(..step))
    },
    map: lq.color.map.viridis,
    color: (x, y, u, v) => calc.norm(..lq.vec.subtract(
      if y == 0 { (0, 0) } else { gcd_step(x, y) },
      (x, y),
    )),
    scale: 1,
    pivot: start,
  )

  show: lq.set-diagram(height: 9cm, width: 9cm)
  lq.diagram(
    xlabel: [$a$],
    ylabel: lq.label([$b$], angle: 0deg),
    title: [Divergenzfeld des Euklidischen Algorithmus],
    xaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    yaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    force-field,
  )
  h(5pt)
  lq.colorbar(force-field)
}

#let visualize_gcd_steps_needed(x-coordinates, y-coordinates) = {
  let color-mesh = lq.colormesh(
    x-coordinates,
    y-coordinates,
    (x, y) => gcd_steps_needed(x, y),
    map: lq.color.map.viridis,
  )

  show: lq.set-diagram(height: 9cm, width: 9cm)
  lq.diagram(
    xlabel: [$a$],
    ylabel: lq.label([$b$], angle: 0deg),
    title: [Benötigte Schritte des Euklidischen Algorithmus],
    xaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    yaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    color-mesh,
  )
  h(5pt)
  lq.colorbar(color-mesh, label: [Anzahl Schritte = $T(a, b)$])
}

#let visualize_gcd_results(x-coordinates, y-coordinates) = {
  show: lq.set-diagram(height: 9cm, width: 9cm)

  let color-mesh = lq.colormesh(
    x-coordinates,
    y-coordinates,
    (x, y) => gcd_steps(x, y).last().at(0) + 1,
    map: lq.color.map.viridis,
    norm: "log",
  )

  lq.diagram(
    xlabel: [$a$],
    ylabel: lq.label([$b$], angle: 0deg),
    title: [Ergebnis des Euklidischen Algorithmus],
    xaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    yaxis: (mirror: false, stroke: color.white.transparentize(100%)),
    color-mesh,
  )
  h(5pt)
  lq.colorbar(color-mesh, label: [$gcd(a, b)$])
}

#let visualize_gcd_complexity(a-range) = {
  lq.diagram(
    width: 11cm,
    height: 8cm,
    title: [Zeitkomplexität des Euklidischen Algorithmus],
    xlabel: [$a$],
    ylabel: [Anzahl Schritte = $limits(max)_(i=0)^(a-1) T(a, i)$],
    lq.plot(a-range, a => range(a)
      .map(b => gcd_steps_needed(a, b))
      .reduce(calc.max)),
  )
}
