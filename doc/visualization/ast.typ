#import "@preview/cetz:0.3.4": canvas, draw, tree

#let visualize-ast(ast) = {
  set text(font: "JetBrainsMono NF")
  canvas({
    import draw: *

    tree.tree(
      ast,
      spread: 2.5,
      grow: 1.5,
      draw-node: (node, ..) => {
        circle((), radius: .4, stroke: 1pt)
        content((), node.content)
      },
    )
  })
}
