#import "deps.typ": *
#import "libs.typ": *
#import "template.typ": documentation-template

#set document(
  author: "Tim Peko",
  title: "SWP4 - Übung 2",
)
#show: documentation-template.with(
  semester-term: "SS 2026",
  aufwand-in-h: "10",
  student-id: "2420458029",
)
#pdf.attach(
  "../WEB4_Projekt_Angabe.pdf",
  mime-type: "application/pdf",
  relationship: "source",
  description: "Projektangabe",
)

#import "visualization/code_metrics.typ": *
#import "visualization/test_results.typ": *

// Show rule for raw blocks
#show raw.where(lang: "pintora"): it => pintora-diagram(it.text)
#show raw.where(lang: "graphviz"): diagraph.raw-render.with()



