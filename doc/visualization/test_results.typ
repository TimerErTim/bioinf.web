#import "../libs.typ": *
#import "../theme.typ": colors

#let table-test-results-overview(data) = {
  let entry(name, tests, disabled, failures, errors) = {
    let tests = tests - disabled
    let failures = failures + errors
    let color = if failures > 0 { colors.red.rgb.lighten(50%) } else {
      colors.green.rgb.lighten(60%)
    }

    (
      table.cell(fill: color, name),
      table.cell(fill: color)[#tests],
      table.cell(fill: color)[#failures],
    )
  }

  table(
    columns: 3,
    table.header[*Testsuite*][*Tests*][*Fehler*],
    ..entry([*Total*], data.tests, data.disabled, data.failures, data.errors),
    table.hline(start: 0, end: 3, stroke: 2pt),
    ..for testsuite in data.testsuites {
      entry(
        testsuite.name,
        testsuite.tests,
        testsuite.disabled,
        testsuite.failures,
        testsuite.errors,
      )
    },
  )
}

#let table-test-results-detailed(data) = {
  let entry(testcase) = {
    // Determine color and symbol using if-cascaded logic, prioritizing status
    let (color, symbol) = if testcase.status == "passed" {
      (colors.green.rgb, sym.checkmark)
    } else if testcase.status == "failed" {
      (colors.red.rgb, sym.crossmark)
    } else if testcase.status == "error" {
      (colors.red.rgb, sym.crossmark)
    } else {
      (colors.gray.rgb, sym.quest)
    }

    (
      table.cell(
        fill: color.lighten(60%),
        inset: (left: 0.5cm),
        align: horizon,
      )[
        #set text(lang: "en", hyphenate: true, overhang: false, size: 10pt)
        #layout(size => {
          let display_text = testcase.name
          let truncated = false
          while (
            measure(display_text + if truncated { "..." }).width > size.width
          ) {
            display_text = display_text.slice(0, -1)
            truncated = true
          }
          return display_text + if truncated { "..." }
        })
      ],
      [
        #text(fill: color, symbol)
        #if testcase.status == "passed" {
          "Erfolgreich"
        } else if testcase.status == "failed" {
          "Fehlgeschlagen"
        } else if testcase.status == "error" {
          "Fehler"
        } else {
          "Übersprungen"
        }
      ],
      [#testcase.time],
    )
  }

  table(
    columns: 3,
    table.header(repeat: true,)[*Testname*][*Status*][*Zeit*],
    ..for testsuite in data.testsuites {
      (
        table.header(repeat: true, level: 2, table.cell(
          colspan: 3,
        )[*#testsuite.name*]),
      )
      for testcase in testsuite.testsuite {
        entry(testcase)
      }
    },
  )
}

#let table-test-results-overview-cpp(data) = {
  table-test-results-overview(data)
}

#let table-test-results-detailed-cpp(data) = {
  table-test-results-detailed((
    ..data,
    testsuites: data.testsuites.map(it => {
      return (
        ..it,
        testsuite: it.testsuite.map(it => {
          let status = "passed"
          if it.status != "RUN" {
            status = "failed"
          }
          if "failures" in it {
            status = "failed"
          }
          if "errors" in it {
            status = "error"
          }

          return (..it, status: status)
        }),
      )
    }),
  ))
}

#let _find-junit-intellijrunner-suites-subcases(test-results) = {
  return test-results
    .first()
    .children
    .filter(child => type(child) == dictionary and child.tag == "suite")
    .map(it => {
      let find_child_tests(child) = (
        ..child.children.filter(child => (
          type(child) == dictionary and child.tag == "test"
        )),
        ..child
          .children
          .filter(child => type(child) == dictionary)
          .map(find_child_tests)
          .flatten(),
      )
      let all_subtests = find_child_tests(it)
      (suite: it, subcases: all_subtests)
    })
}

#let table-test-results-overview-junit-intellijrunner(test-results) = {
  let data = (
    tests: null-default(
      apply-nullsafe(
        test-results
          .first()
          .children
          .find(child => (
            type(child) == dictionary and child.attrs.name == "total"
          )),
        it => int(it.attrs.value),
      ),
      0,
    ),
    failures: null-default(
      apply-nullsafe(
        test-results
          .first()
          .children
          .find(child => (
            type(child) == dictionary and child.attrs.name == "failures"
          )),
        it => int(it.attrs.value),
      ),
      0,
    ),
    errors: null-default(
      apply-nullsafe(
        test-results
          .first()
          .children
          .find(child => (
            type(child) == dictionary and child.attrs.name == "errors"
          )),
        it => int(it.attrs.value),
      ),
      0,
    ),
    disabled: null-default(
      apply-nullsafe(
        test-results
          .first()
          .children
          .find(child => (
            type(child) == dictionary and child.attrs.name == "disabled"
          )),
        it => int(it.attrs.value),
      ),
      0,
    ),
    testsuites: _find-junit-intellijrunner-suites-subcases(test-results).map((
      (suite, subcases),
    ) => {
      (
        name: suite.attrs.name,
        tests: subcases.len(),
        failures: subcases.filter(it => it.attrs.status == "failed").len(),
        errors: subcases.filter(it => it.attrs.status == "error").len(),
        disabled: subcases.filter(it => it.attrs.status == "disabled").len(),
      )
    }),
  )
  table-test-results-overview(data)
}

#let table-test-results-detailed-junit-intellijrunner(
  test-results,
  map-name: it => it.split(": ").join("\n"),
) = {
  let data = _find-junit-intellijrunner-suites-subcases(test-results).map((
    (suite, subcases),
  ) => {
    (
      name: suite.attrs.name,
      testsuite: subcases.map(it => (
        name: map-name(it.attrs.name),
        status: it.attrs.status,
        time: [#it.attrs.at("duration", default: 0) ms],
      )),
    )
  })
  table-test-results-detailed((testsuites: data))
}

#let _map-junit-custom-gradle-data(data) = {
  let grouped = (:)
  for test in data {
    if test.className not in grouped {
      grouped.insert(test.className, ())
    }
    grouped.at(test.className).push(test)
  }
  return (
    tests: data.len(),
    failures: data.filter(t => t.result == "FAILURE").len(),
    errors: 0,
    disabled: data.filter(t => t.result == "SKIPPED").len(),
    testsuites: grouped
      .pairs()
      .map(((k, tests)) => (
        name: k.split(".").last(),
        tests: tests.len(),
        failures: tests.filter(t => t.result == "FAILURE").len(),
        errors: 0,
        disabled: tests.filter(t => t.result == "SKIPPED").len(),
        testsuite: tests.map(t => (
          name: t.name,
          status: if t.result == "SUCCESS" { "passed" } else if t.result
            == "FAILURE" { "failed" } else { "disabled" },
          time: str(t.duration) + " ms",
        )),
      )),
  )
}

#let table-test-results-overview-junit-custom-gradle(data) = {
  table-test-results-overview(_map-junit-custom-gradle-data(data))
}

#let table-test-results-detailed-junit-custom-gradle(data) = {
  table-test-results-detailed(_map-junit-custom-gradle-data(data))
}
