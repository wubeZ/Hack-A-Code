def sayLove():
  tests = int(input());
  for _ in range(tests):
    one, two = map(int, input().split())
    print(one + two)

sayLove();
