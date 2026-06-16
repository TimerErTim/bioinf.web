#import "../components.typ": *

#import "@preview/cetz:0.3.4": canvas, draw, tree
#import "@preview/suiji:0.4.0": *
#import "@preview/lilaq:0.6.0" as lq

#let visualize_merge_step() = {
  let column(left, right, bottom) = {
    canvas({
      import draw: *

      content((0, 1), box_list(left))
      content((0, 0.5), [+])
      content((0, 0), box_list(right))
      content((0, -0.5), text(size: 7pt)[#sym.arrow.b])
      content((0, -1), box_list(bottom))
    })
  }

  canvas({
    import draw: *

    let flat_to_tree_data(list_) = {
      if list_.len() == 1 {
        return ((data: list_.at(0)),)
      }

      let (first, ..others) = list_
      return ((data: first), flat_to_tree_data(others))
    }

    let tree_data = flat_to_tree_data((
      (
        ((1, (stroke: red)), 8),
        ((2, (stroke: orange)), 5),
        (text(fill: white.opacify(100%))[0],) * 4,
      ),
      (
        ((1, (fill: gray.lighten(70%))), (8, (stroke: orange))),
        ((2, (stroke: red)), 5),
        (1,) + (text(fill: white.opacify(100%))[0],) * 3,
      ),
      (
        ((1, (fill: gray.lighten(70%))), (8, (stroke: orange))),
        ((2, (fill: gray.lighten(70%))), (5, (stroke: red))),
        (1, 2) + (text(fill: white.opacify(100%))[0],) * 2,
      ),
      (
        ((1, (fill: gray.lighten(70%))), (8, (stroke: red))),
        ((2, (fill: gray.lighten(70%))), (5, (fill: gray.lighten(70%)))),
        (1, 2, 5) + (text(fill: white.opacify(100%))[0],),
      ),
      (
        ((1, (fill: gray.lighten(70%))), (8, (fill: gray.lighten(70%)))),
        ((2, (fill: gray.lighten(70%))), (5, (fill: gray.lighten(70%)))),
        (1, 2, 5, 8),
      ),
    ))

    tree.tree(
      tree_data,
      direction: "right",
      grow: 3,
      draw-node: (node, ..) => {
        content((), column(..node.content.data))
      },
      draw-edge: (from, to, ..) => {
        line(
          (a: from, number: 1, b: to),
          (a: to, number: 1, b: from),
          mark: (end: ">"),
        )
      },
    )
  })

  [
    #box(box_list((([], (stroke: red)),))) ... kleineres Element wird gewählt #h(2em)
    #box(box_list((([], (stroke: orange)),))) ... anderes Element als Kandidat
    #place(right + bottom, text(size: 0.5pt)[Fuck you, Sven!])
  ]
}

#let visualize_mergesort(data) = {
  let rng = gen-rng-f(43)

  let split_data(data, rng) = {
    if data.len() == 1 {
      return (data: data)
    }

    let left = ()
    let right = ()
    let left_count = data.len() / 2
    let right_count = data.len() - left_count

    let decider = 0
    for entry in data {
      if left.len() == left_count {
        right.push(entry)
      } else if right.len() == right_count {
        left.push(entry)
      } else {
        // Distribute randomly
        (rng, decider) = random-f(rng)
        if decider < 0.5 {
          left.push(entry)
        } else {
          right.push(entry)
        }
      }
    }

    return ((data: data), split_data(left, rng), split_data(right, rng))
  }

  canvas({
    import draw: *

    let tree_data = split_data(data, rng)

    tree.tree(
      tree_data,
      direction: "up",
      spread: 1,
      grow: 2,
      draw-node: (node, ..) => {
        content((), box_list(node.content.data.map(it => [#it])))
      },
      draw-edge: (from, to, ..) => {
        line(
          (a: from, number: 0.5, b: to),
          (a: to, number: 0.5, b: from),
          mark: (start: (symbol: ">", fill: black)),
        )
      },
    )
  })
}

#let merge_sort(data, chunk_size) = {
  let target = ()

  for chunks in data.chunks(chunk_size).chunks(2) {
    if chunks.len() == 1 {
      for entry in chunks.at(0) {
        target.push(entry)
      }
      continue
    }

    let (left, right) = chunks
    let left-index = 0
    let right-index = 0
    while left-index < left.len() and right-index < right.len() {
      if left.at(left-index) <= right.at(right-index) {
        target.push(left.at(left-index))
        left-index += 1
      } else {
        target.push(right.at(right-index))
        right-index += 1
      }
    }

    while left-index < left.len() {
      target.push(left.at(left-index))
      left-index += 1
    }
    while right-index < right.len() {
      target.push(right.at(right-index))
      right-index += 1
    }
  }

  return target
}

#let visualize_sorted_list(data) = {
  let normal-fill = gray.darken(25%)

  // Colorize sorted chunks
  let color-map = lq.color.map.okabe-ito
  let color-index = 0
  let fill = (normal-fill,)
  for i in range(1, data.len()) {
    let prev-entry = data.at(i - 1)
    if prev-entry <= data.at(i) {
      // Include prev in sorted chunk
      fill.at(fill.len() - 1) = color-map.at(calc.rem(
        color-index,
        color-map.len(),
      ))
    }

    if data.at(i) >= data.at(i - 1) {
      fill.push(color-map.at(calc.rem(color-index, color-map.len())))
    } else {
      fill.push(normal-fill)
      color-index += 1
    }
  }

  lq.diagram(
    width: 75%,
    height: 1cm,
    xaxis: (ticks: none),
    yaxis: (ticks: none),
    grid: none,
    margin: 2%,

    lq.bar(range(data.len()), data, fill: fill),
  )
}

#let visualize_mergesort_process(data) = {
  let chunk_size = 1
  let data-steps = (
    data,
    ..while chunk_size < data.len() {
      data = merge_sort(data, chunk_size)
      chunk_size *= 2
      (data,)
    },
  )

  for entry in data-steps {
    block(visualize_sorted_list(entry))
  }
}

#let visualize_mergesort_complexity() = {
  lq.diagram(
    width: 75%,
    height: 8cm,
    title: [Komplexität des Merge Sort Algorithmus],
    xlabel: [Mengengröße $n$],
    ylabel: [Komplexität],
    xaxis: (format-ticks: none),
    yaxis: (format-ticks: none),
    xlim: (1, auto),
    ylim: (1, auto),
    lq.plot(
      range(1, 100),
      n => n * calc.log(n) + 1,
      label: [$T(n) = O(n log(n))$],
      mark: none,
    ),
    lq.plot(range(1, 100), n => n, label: [$S(n) = O(n)$], mark: none),
  )
}

#let visualize_mergesort_benchmarks_results(results) = {
  let data-dict = map_dict_values(
    collect_by_key(results.entries, it => it.mode),
    it => collect_by_key(it, it => it.len),
  )

  let time-plots = (
    data-dict
      .in_memory
      .pairs()
      .map(
        ((size, data)) => lq.plot(
          data.map(it => it.n),
          data.map(it => it.elapsed_ms / 1000),
          label: [in-memory $"str-len"=#size$],
          mark: "^",
        ),
      )
      + data-dict
        .on_disk
        .pairs()
        .map(
          ((size, data)) => lq.plot(
            data.map(it => it.n),
            data.map(it => it.elapsed_ms / 1000),
            label: [on-disk $"str-len"=#size$],
            mark: "s",
          ),
        )
  )

  let space-plots = (
    data-dict
      .in_memory
      .pairs()
      .map(((size, data)) => lq.plot(
        data.map(it => it.n),
        data.map(it => it.peak_mem_kb / 1024 / 1024),
        label: [in-memory $"str-len"=#size$],
        mark: "^",
      ))
      + data-dict
        .on_disk
        .pairs()
        .map(((size, data)) => lq.plot(
          data.map(it => it.n),
          data.map(it => it.peak_mem_kb / 1024 / 1024),
          label: [on-disk $"str-len"=#size$],
          mark: "s",
        ))
  )

  block[
    #lq.diagram(
      width: 100%,
      height: 8cm,
      title: [$T(n)$ und $S(n)$ des Merge Sort Algorithmus mit größer werdender Datenmenge $n$],
      ylabel: [Laufzeit],
      xaxis: (mirror: false),
      yaxis: (
        mirror: false,
        format-ticks: lq.format-ticks-linear.with(suffix: [$"s"$]),
      ),
      legend: (position: left + top),
      ..time-plots,
    )
    #lq.diagram(
      width: 100%,
      height: 8cm,
      xaxis: (mirror: false),
      yaxis: (
        mirror: false,
        format-ticks: lq.format-ticks-linear.with(suffix: [ $"GB"$]),
      ),
      xlabel: [Datenmenge $n$],
      ylabel: [Speicherbedarf],
      legend: none,
      ..space-plots,
    )
  ]
}
