#import "../theme.typ": colors

#let _is-element(node) = {
  type(node) == dictionary and "tag" in node
}

#let _collect-testcases(nodes) = {
  let cases = ()
  for node in nodes {
    if not _is-element(node) {
      continue
    }
    if node.tag == "testcase" {
      cases.push(node)
    }
    if "children" in node and node.children != none {
      cases += _collect-testcases(node.children)
    }
  }
  cases
}

#let _parse-assertion(name) = {
  if name.contains(" == ") {
    let parts = name.split(" == ")
    return (field: parts.at(0), op: "==", expected: parts.at(1))
  }
  if name.contains(" includes ") {
    let parts = name.split(" includes ")
    return (field: parts.at(0), op: "includes", expected: parts.at(1))
  }
  return (field: name, op: "", expected: "")
}

#let _observed-from-failure(message) = {
  let m = message.match(regex("\\(([^)]+)\\)"))
  if m != none {
    return m.captures.at(0)
  }
  return message
}

#let _status-of(testcase) = {
  if "children" not in testcase or testcase.children == none {
    return "passed"
  }
  for child in testcase.children {
    if _is-element(child) and (child.tag == "failure" or child.tag == "error") {
      return "failed"
    }
  }
  return "passed"
}

#let _failure-message(testcase) = {
  if "children" not in testcase or testcase.children == none {
    return none
  }
  for child in testcase.children {
    if _is-element(child) and (child.tag == "failure" or child.tag == "error") {
      return child.attrs.at("message", default: "")
    }
  }
  return none
}

#let parse-httpyac-junit(path) = {
  let root = xml(path)
  let suites = ()
  for node in root {
    if not _is-element(node) {
      continue
    }
    if node.tag == "testsuite" {
      suites.push(node)
    }
    if node.tag == "testsuites" and "children" in node {
      for child in node.children {
        if _is-element(child) and child.tag == "testsuite" {
          suites.push(child)
        }
      }
    }
  }

  let all-cases = ()
  for suite in suites {
    all-cases += _collect-testcases((suite,))
  }

  let grouped = (:)
  for suite in suites {
    let suite-key = suite.attrs.at("package", default: suite.attrs.at("name", default: "REST"))
    if suite-key not in grouped {
      grouped.insert(suite-key, ())
    }
    if "children" in suite and suite.children != none {
      for child in suite.children {
        if _is-element(child) and child.tag == "testcase" {
          grouped.at(suite-key).push(child)
        }
      }
    }
  }

  let total = all-cases.len()
  let failures = all-cases.filter(tc => _status-of(tc) == "failed").len()

  (
    total: total,
    failures: failures,
    errors: 0,
    disabled: 0,
    suites: grouped,
    all-cases: all-cases,
  )
}

#let table-httpyac-overview(data) = {
  table(
    columns: 3,
    table.header[*Testsuite*][*Tests*][*Fehler*],
    table.cell(fill: if data.failures > 0 {
      colors.red.rgb.lighten(50%)
    } else {
      colors.green.rgb.lighten(60%)
    })[*Total*],
    table.cell(fill: if data.failures > 0 {
      colors.red.rgb.lighten(50%)
    } else {
      colors.green.rgb.lighten(60%)
    })[#data.total],
    table.cell(fill: if data.failures > 0 {
      colors.red.rgb.lighten(50%)
    } else {
      colors.green.rgb.lighten(60%)
    })[#data.failures],
    table.hline(start: 0, end: 3, stroke: 2pt),
    ..for (suite-name, cases) in data.suites.pairs() {
      let fails = cases.filter(tc => _status-of(tc) == "failed").len()
      let color = if fails > 0 {
        colors.red.rgb.lighten(50%)
      } else {
        colors.green.rgb.lighten(60%)
      }
      (
        table.cell(fill: color)[#suite-name],
        table.cell(fill: color)[#cases.len()],
        table.cell(fill: color)[#fails],
      )
    },
  )
}

#let table-httpyac-detailed(data) = {
  let row(testcase) = {
    let parsed = _parse-assertion(testcase.attrs.at("name", default: ""))
    let status = _status-of(testcase)
    let failure = _failure-message(testcase)
    let observed = if status == "passed" {
      parsed.expected
    } else if failure != none {
      _observed-from-failure(str(failure))
    } else {
      [—]
    }
    let (color, label) = if status == "passed" {
      (colors.green.rgb, [OK])
    } else {
      (colors.red.rgb, [Fehler])
    }

    (
      table.cell(fill: color.lighten(60%))[
        #testcase.attrs.at("classname", default: "—")
      ],
      table.cell(fill: color.lighten(60%))[
        #parsed.field #if parsed.op != "" { parsed.op } #parsed.expected
      ],
      table.cell(fill: color.lighten(60%))[
        #parsed.expected
      ],
      table.cell(fill: color.lighten(60%))[
        #observed
      ],
      table.cell(fill: color.lighten(60%))[
        #text(fill: color, label)
      ],
    )
  }

  table(
    columns: 5,
    table.header(repeat: true)[*Request*][*Prüfung*][*Erwartet*][*Beobachtet*][*Status*],
    ..for (suite-name, cases) in data.suites.pairs() {
      (
        table.header(repeat: true, level: 2, table.cell(
          colspan: 5,
        )[*#suite-name*]),
      )
      for testcase in cases {
        row(testcase)
      }
    },
  )
}
