<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class JwtKey extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'client_id' => 'democlient',
                'public_key' => '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnZn5FSMEvgk+aD5QRsT6
NgmqwZXiCz7HZnV7SDMb4pvhPivjfFc0/rRtzU5Uru8AwZ3s3Lo1E2lzWywymidJ
HYcab1VXh6H0moadht6pi5w5KCu0ELrw+6jg+Iq326oaL+W6lP/QzoC0smHvBaYn
0t0/KXjGiLEKl1NXuR09hAXc0PQ5TUuu6yXcnunDCrQG4Crx+eQnaz/mRRVc2bfA
uCFfRuefau9G9ZbkgrcegndeSPcjl8YdhAAiDu54O/PRMpyVr9QujOaiUL6jadmG
FSb+1wludjwN3J3rS27SHBVclLQwDKrV9szjEKj4M+EGuWXtji3ijy39uQDLtnqS
RwIDAQAB
-----END PUBLIC KEY-----',
                'private_key' => '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAnZn5FSMEvgk+aD5QRsT6NgmqwZXiCz7HZnV7SDMb4pvhPivj
fFc0/rRtzU5Uru8AwZ3s3Lo1E2lzWywymidJHYcab1VXh6H0moadht6pi5w5KCu0
ELrw+6jg+Iq326oaL+W6lP/QzoC0smHvBaYn0t0/KXjGiLEKl1NXuR09hAXc0PQ5
TUuu6yXcnunDCrQG4Crx+eQnaz/mRRVc2bfAuCFfRuefau9G9ZbkgrcegndeSPcj
l8YdhAAiDu54O/PRMpyVr9QujOaiUL6jadmGFSb+1wludjwN3J3rS27SHBVclLQw
DKrV9szjEKj4M+EGuWXtji3ijy39uQDLtnqSRwIDAQABAoIBAAXmHnmZDvNh+zEB
rBWKB+d/4yFNz+El32bJXOzt5MxNk1e1YtVjmjsyW63Ekczea0PT+mqkmZn5Z3Tx
KY+1iroNNYMXSahs+m/SPBExQl1UYptLT3kU1jJFNu52mr0hF8vVKG+tR46DKMBI
hB7NAk6VM2kE+UFihwf0+aKXIpdPZdLCZzkGrkwThhNtc6iBF3ZtEyfrF9lu5U1H
bkvhJ3YDf8FvwFlKnAAiQn0IjG78KrtJuk3R5zBHfIV9Sy2Vvar4HAPAMdQSO2tY
1DW0yjUb73bU4GglfSWKs73L1VUqAUcoNysFiPJxYpXNqZyYHU8SSWCJrQ3R+ABx
Pvn5SQkCgYEAzkVMQCTlCpSuR/QwS24ryd/KPTNvMaozVnEiQi5yHGtROqWlD4vb
z8NTfYE10cYqt6OfTW0MLaIUVc2meTt5mupC+ixsDbhIM5LChdgjYsDXE7PfOrfs
IGmmHb2CO9SfQMlp+ayXJVlhqXIsNn8VFrKIV7LZA9rYtpUH7XOQKEUCgYEAw5ji
8I4p+VBUFO7RW3A0Va5lwlfOoZVZFjpbTUCRKhMa4+6kyf8emhR+MpXna+JUMdE7
SLMAYWjKbD6WTWPxXfhulf8ogucwYkM6rLKwnabztP9v/tRX7s13Mwvu+VGnUkEQ
JzfS/R1U/jeqNxYx6M5yq//1APzcwiPyP+vZtxsCgYBIeZrQEuaTMCiISalKZ5xl
IweZN2BDcAz3u2qRHGaly6NP2vHDI54JQxUrzOGPInTR72DCwKT9x6wjdc2fXSFe
KB9+bWtN6skbrd5BTu8n/J/VYWb257bEsLpYSsTepteJ2PsuadD1o5EoC2CziTVs
szAquEF+FD/+7yBI80jcjQKBgH43P+oesrMwxLElUm+AviSz/vIjmzDloLEZQyVc
js/puCZxLmEygVrH8o9N8CHL0Ky86VaGO66f4k9rK64Q2gXbc/DS0B94c95Up54d
SJ7Sxtx7IJf+CigM6ehEwRadPC110qib+0heDyRkYzBc4CPsZE7dQgo7IXEdpQre
dIUVAoGBAJJqqigejLCA8G+RkkbAWzw9+3HTxsOIHFGGz+B8DRCAJpNNPO2Y4qxx
3gkpIMpSCCz7pFY0xu3H/4Rf40dLplpk0Y8tW8MYBqZK4FYKLUPKb3hOoeyywsmz
iVMXftl/nwNBQqGD9sLdXMlJqdpKwNHLWJjiyfWsXoydhUwQhoFR
-----END RSA PRIVATE KEY-----
',
                'encryption_algorithm' => 'RS256',
            ],
        ];

        $posts = $this->table('oauth_public_keys');
        $posts->insert($data)->save();
    }
}