
import subprocess

counter = 0
symfony_input_file = "tgwf-check.csv"
initial_domain_list = "tgwf-check.1m.csv"

symfony_csv_check_command = "./bin/console tgwf:greencheck:csvchecker"

while counter < 200_000:
    lower = counter
    upper = counter + 49_999

    with open("tgwf-check.1m.csv", "r") as big_file:
        # we assume we're working with a fle of less than say… 30mb here,
        # as the Alexa top 1m sites is only 22mb for a 1 million.
        # if we have more, because we're doig it in sequence, we can use
        # the singular readline(), and count upwards in batchs of 50k
        # instead rather loading in the whole file with readlines,
        # then array slicing

        lines = big_file.readlines()

        with open(symfony_input_file, "w") as f:
            for line in lines[lower:upper]:
                f.write(line)

            check_output_with_tail = f"tail -n 5 {symfony_input_file}"

            subprocess.run(check_output_with_tail, shell=True, check=True)
            subprocess.run(symfony_csv_check_command, shell=True, check=True)

    counter += 50_000