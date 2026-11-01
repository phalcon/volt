# Lessons

## Timing assertions must not depend on the machine

**What went wrong:** `LongListTest` copied cphalcon's "40,000-element array compiles in under 3 s" check.
It passed locally (0.25 s) but failed in the CI coverage job (4.0 s). The limit was sized for native C,
while the coverage job runs this PHP parser under xdebug coverage, which is about 13 times slower, on a
slower GitHub runner.

**Rule:** never assert an absolute wall-clock limit in a test that CI may run under coverage or on
shared runners. To guard against a complexity regression, compare two runs on the same machine instead:
time N and 8N and assert the ratio. Linear is about 8, quadratic up to 64, and the threshold sits between
them. Before trusting such a test, show that it fails on a slow implementation and passes under
`XDEBUG_MODE=coverage` with a throttled CPU (`docker run --cpus=0.3`).
