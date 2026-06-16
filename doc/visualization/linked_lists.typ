#import "@preview/fletcher:0.5.8" as fletcher: edge, node

#let visualize-doubly-linked-list-sentinel-structure() = {
  fletcher.diagram(
    node-corner-radius: 0pt,
    node-shape: rect,
    node(`head_`),
    node((4, 0), `tail_`),
    edge((0, 0), (0, 1), "-"),
    edge((4, 0), (4, 1), "-"),

    node((0, 1), [Sentinel\ head], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((1, 1), [Node\ A], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((2, 1), [Node\ B], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((3, 1), [Node\ C], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((4, 1), [Sentinel\ tail], stroke: 1pt),
  )
}

#let visualize-doubly-linked-list-iterator-structure() = {
  fletcher.diagram(
    node-corner-radius: 0pt,
    node-shape: rect,
    node((1, 0), `Node<T>* current_`),
    edge((1, 0), (1, 1), "-|>"),

    node((0, 1), [prev], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node(
      (1, 1),
      grid(
        columns: 1,
        stroke: 1pt,
        inset: 2mm,
        [Node],
        pad(`T data`, y: 1mm),
      ),
      inset: 0cm,
    ),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((2, 1), [next], stroke: 1pt),
  )
}

#let vis-dll-iterator-deletion-danger() = {
  fletcher.diagram(
    node-shape: rect,
    debug: false,

    node((1, 0), `Iterator it`),
    edge((1, 0), (1, 1), "-|>"),

    node((0, 1), [A], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node(
      (1, 1),
      [B
        #set line(stroke: red, length: 28pt)
        #place(left + top, dx: -6pt, dy: -6pt, line(angle: 45deg))
        #place(bottom + left, dx: -6pt, dy: 6pt, line(angle: -45deg))
      ],
      stroke: 1pt,
    ),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((2, 1), [C], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((3, 1), [D], stroke: 1pt),

    node((1, 1.75), [`erase(it)`], name: <erase-it-node>),
    edge((1, 1.75), (1, 1.25), "=>"),
    node(
      <erase-it-node.east>,
      context {
        let body = [#sym.arrow `it` wird ungültig]
        let size = measure(body)
        h(size.width)
        body
      },
      pos: right,
    ),
  )
}

#let vis-dll-stable-iterator-architecture() = {
  fletcher.diagram(
    debug: 0,
    node-shape: rect,
    spacing: 1em,

    node((0, -1), `DoublyLinkedList`, inset: 0pt),
    edge("-|>"),
    edge("l,dd", (0.1, 1), "-|>"),

    node((0, 0), `active_iter`, stroke: 1pt, inset: 3mm),
    edge("-|>"),
    node((2, 0), "Iterator 1"),
    edge("-|>"),
    edge(
      (2, 1),
      "-|>",
      label: text(fill: red, emoji.crossmark),
      label-side: center,
      label-fill: white.transparentize(100%),
    ),
    edge((3, 1), "-|>", bend: 20deg),
    node((4, 0), "Iterator 2"),
    edge("-|>"),
    edge((4, 1), "-|>"),
    node((6, 0), "..."),

    node((0.2, 1), [A], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((1, 1), [B], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((2, 1), [C], stroke: 1pt, name: <c-node>),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    edge(
      <c-node.south-east>,
      <c-node.north-west>,
      "-",
      stroke: red + 2pt,
      layer: 1,
    ),
    edge(
      <c-node.south-west>,
      <c-node.north-east>,
      "-",
      stroke: red + 2pt,
      layer: 1,
    ),
    node((3, 1), [D], stroke: 1pt),
    edge("-|>", shift: 1mm),
    edge("<|-", shift: -1mm),
    node((4, 1), [E], stroke: 1pt),
  )
}
